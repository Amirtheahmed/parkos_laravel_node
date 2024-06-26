import { Channel, Connection, ConsumeMessage } from 'amqplib';
import { RabbitMQConsumer, MessageProcessor, EmailNotificationHandler, PaymentStatuses, ReservationDetails } from '../consumer';
import * as amqp from 'amqplib';

jest.mock('amqplib');

describe('RabbitMQConsumer', () => {
    let connectionMock: jest.Mocked<Connection>;
    let channelMock: jest.Mocked<Channel>;
    let connectSpy: jest.SpyInstance;
    let consoleLogSpy: jest.SpyInstance;
    let consoleErrorSpy: jest.SpyInstance;

    beforeEach(() => {
        connectionMock = {
            createChannel: jest.fn(),
            close: jest.fn(),
        } as unknown as jest.Mocked<Connection>;

        channelMock = {
            assertQueue: jest.fn(),
            consume: jest.fn(),
            ack: jest.fn(),
            nack: jest.fn(),
            close: jest.fn(),
        } as unknown as jest.Mocked<Channel>;

        (amqp.connect as jest.Mock).mockResolvedValue(connectionMock);
        connectionMock.createChannel.mockResolvedValue(channelMock);

        connectSpy = jest.spyOn(amqp, 'connect');
        consoleLogSpy = jest.spyOn(console, 'log').mockImplementation(() => {});
        consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
    });

    afterEach(async () => {
        jest.clearAllMocks();
        await connectionMock.close();
        await channelMock.close();
        consoleLogSpy.mockRestore();
        consoleErrorSpy.mockRestore();
    });

    it('should connect to RabbitMQ and start consuming messages', async () => {
        const consumer = new RabbitMQConsumer('amqp://localhost');
        await consumer.start();

        expect(connectSpy).toHaveBeenCalledWith('amqp://localhost');
        expect(connectionMock.createChannel).toHaveBeenCalled();
        expect(channelMock.assertQueue).toHaveBeenCalledWith('reservation_email_queue', { durable: true });
        expect(channelMock.consume).toHaveBeenCalledWith('reservation_email_queue', expect.any(Function), { noAck: false });
    });

    it('should retry connection on failure', async () => {
        (amqp.connect as jest.Mock).mockRejectedValueOnce(new Error('Connection failed'));

        const consumer = new RabbitMQConsumer('amqp://localhost');
        const startPromise = consumer.start();

        // Allow some time for retries
        await new Promise(resolve => setTimeout(resolve, 6000));

        expect(connectSpy).toHaveBeenCalledTimes(2);

        (amqp.connect as jest.Mock).mockResolvedValueOnce(connectionMock);
        await startPromise;

        expect(connectionMock.createChannel).toHaveBeenCalled();
    }, 10000);
});

describe('MessageProcessor', () => {
    let channelMock: jest.Mocked<Channel>;
    let handlerMock: jest.Mocked<EmailNotificationHandler>;
    let processor: MessageProcessor;

    beforeEach(() => {
        channelMock = {
            ack: jest.fn(),
            nack: jest.fn(),
        } as unknown as jest.Mocked<Channel>;

        handlerMock = {
            handle: jest.fn(),
        };

        processor = new MessageProcessor(channelMock, handlerMock);
    });

    it('should process a valid message', () => {
        const reservationDetails: ReservationDetails = { email: 'test@example.com', status: PaymentStatuses.PAID };
        const message: ConsumeMessage = {
            content: Buffer.from(JSON.stringify(reservationDetails)),
        } as ConsumeMessage;

        processor.processMessage(message);

        expect(handlerMock.handle).toHaveBeenCalledWith(reservationDetails);
        expect(channelMock.ack).toHaveBeenCalledWith(message);
    });

    it('should nack a message if processing fails', () => {
        const invalidMessage: ConsumeMessage = {
            content: Buffer.from('invalid json'),
        } as ConsumeMessage;

        processor.processMessage(invalidMessage);

        expect(handlerMock.handle).not.toHaveBeenCalled();
        expect(channelMock.nack).toHaveBeenCalledWith(invalidMessage);
    });
});

describe('EmailNotificationHandler', () => {
    let handler: EmailNotificationHandler;

    beforeEach(() => {
        handler = new EmailNotificationHandler();
    });

    it('should log when status is PAID', () => {
        const consoleSpy = jest.spyOn(console, 'log').mockImplementation(() => {});

        const reservationDetails: ReservationDetails = { email: 'test@example.com', status: PaymentStatuses.PAID };

        handler.handle(reservationDetails);

        expect(consoleSpy).toHaveBeenCalledWith('Triggering email to test@example.com');

        consoleSpy.mockRestore();
    });

    it('should not log when status is not PAID', () => {
        const consoleSpy = jest.spyOn(console, 'log').mockImplementation(() => {});

        const reservationDetails: ReservationDetails = { email: 'test@example.com', status: PaymentStatuses.PENDING };

        handler.handle(reservationDetails);

        expect(consoleSpy).not.toHaveBeenCalled();

        consoleSpy.mockRestore();
    });
});
