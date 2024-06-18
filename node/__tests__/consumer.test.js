const amqp = require('amqplib');
const { connectToRabbitMQ, processMessage } = require('../index');

jest.mock('amqplib', () => {
    const createChannelMock = jest.fn();
    const ackMock = jest.fn();
    const nackMock = jest.fn();
    const consumeMock = jest.fn((queue, callback) => callback(fakeMessage));

    const fakeMessage = {
        content: Buffer.from(JSON.stringify({ status: 2, email: 'amir@parkos.com' })),
    };

    return {
        connect: jest.fn().mockImplementation(() => {
            return new Promise((resolve, reject) => {
                // Simulate delay for connecting, which helps in testing retry logic
                setTimeout(() => {
                    resolve({
                        createChannel: createChannelMock.mockResolvedValue({
                            assertQueue: jest.fn().mockResolvedValue(true),
                            consume: consumeMock,
                            ack: ackMock,
                            nack: nackMock
                        })
                    });
                }, 500);
            });
        })
    };
});

describe('RabbitMQ Consumer', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        jest.spyOn(console, 'log').mockImplementation(() => {});
        jest.spyOn(console, 'error').mockImplementation(() => {});
    });

    afterEach(() => {
        console.log.mockRestore();
        console.error.mockRestore();
    });

    it('should connect to RabbitMQ and create a channel', async () => {
        await connectToRabbitMQ();
        expect(amqp.connect).toHaveBeenCalledWith('amqp://localhost');
    });

    it('should handle connection retries on failure', async () => {
        amqp.connect
            .mockRejectedValueOnce(new Error('Connection failed'))  // First call fails
            .mockResolvedValueOnce({  // Second call succeeds
                createChannel: () => Promise.resolve({
                    assertQueue: () => Promise.resolve(true),
                    consume: () => Promise.resolve(),
                }),
            });

        await connectToRabbitMQ();
        expect(amqp.connect).toHaveBeenCalledTimes(2);
    }, 10000);

    it('should process a valid message correctly', async () => {
        const channel = {
            ack: jest.fn(),
            nack: jest.fn(),
        };
        const message = {
            content: Buffer.from(JSON.stringify({ status: 2, email: 'amir@parkos.com' })),
        };

        processMessage(message, channel);
        expect(channel.ack).toHaveBeenCalledWith(message);
    });

    it('should nack a message with invalid JSON', async () => {
        const channel = {
            ack: jest.fn(),
            nack: jest.fn(),
        };
        const message = {
            content: Buffer.from('invalid JSON'),
        };

        processMessage(message, channel);
        expect(channel.nack).toHaveBeenCalledWith(message);
    });
});
