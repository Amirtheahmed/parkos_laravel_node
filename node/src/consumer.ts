import amqp, { Channel, Connection, ConsumeMessage } from 'amqplib';

const QUEUE_NAME = 'reservation_email_queue';
const RABBITMQ_URL = 'amqp://localhost';

enum PaymentStatuses {
    PENDING = 1,
    PAID = 2,
    CANCELLED = 3,
    REFUNDED = 4,
}

interface ReservationDetails {
    email: string;
    status: PaymentStatuses;
}

interface IMessageHandler {
    handle(message: ReservationDetails): void;
}

class EmailNotificationHandler implements IMessageHandler {
    handle(message: ReservationDetails): void {
        if (message.status === PaymentStatuses.PAID) {
            console.log(`Triggering email to ${message.email}`);
            // Email sending logic goes here
        }
    }
}

class MessageProcessor {
    private channel: Channel;
    private messageHandler: IMessageHandler;

    constructor(channel: Channel, messageHandler: IMessageHandler) {
        this.channel = channel;
        this.messageHandler = messageHandler;
    }

    processMessage(message: ConsumeMessage | null): void {
        if (message !== null) {
            try {
                const content: string = message.content.toString();
                const reservationDetails: ReservationDetails = JSON.parse(content);
                console.log("Received:", reservationDetails);

                this.messageHandler.handle(reservationDetails);

                this.channel.ack(message);
            } catch (error) {
                console.error('Error processing message', error);
                this.channel.nack(message);
            }
        }
    }
}

class RabbitMQConsumer {
    private readonly rabbitUrl: string;

    constructor(rabbitUrl: string) {
        this.rabbitUrl = rabbitUrl;
    }

    async start(): Promise<void> {
        const connection = await this.connect();
        const channel = await connection.createChannel();

        await channel.assertQueue(QUEUE_NAME, { durable: true });
        console.log(`Queue ${QUEUE_NAME} is ready`);

        const handler = new EmailNotificationHandler();
        const processor = new MessageProcessor(channel, handler);

        await channel.consume(QUEUE_NAME, message => processor.processMessage(message), {noAck: false});
        console.log(`Waiting for messages in ${QUEUE_NAME}. To exit press CTRL+C`);
    }

    private async connect(): Promise<Connection> {
        let connection: Connection | null = null;
        while (!connection) {
            try {
                connection = await amqp.connect(this.rabbitUrl);
                console.log('Connected to RabbitMQ');
            } catch (error) {
                console.error('Failed to connect to RabbitMQ, retrying in 5 seconds', error);
                await new Promise(resolve => setTimeout(resolve, 5000));
            }
        }
        return connection;
    }
}

const consumer = new RabbitMQConsumer(RABBITMQ_URL);
consumer.start();

export { RabbitMQConsumer, MessageProcessor, EmailNotificationHandler, IMessageHandler, PaymentStatuses, ReservationDetails };
