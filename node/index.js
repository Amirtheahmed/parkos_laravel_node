const amqp = require('amqplib');

const QUEUE_NAME = 'reservation_email_queue';
const RABBITMQ_URL = 'amqp://localhost';

const PAYMENT_STATUSES = {
    PENDING:1,
    PAID: 2,
    CANCELLED: 3,
    REFUNDED: 4,
}

async function connectToRabbitMQ() {
    let connection;
    while (!connection) {
        try {
            connection = await amqp.connect(RABBITMQ_URL);
            console.log('Connected to RabbitMQ');
        } catch (error) {
            console.error('Failed to connect to RabbitMQ, retrying in 5 seconds', error);
            await new Promise(resolve => setTimeout(resolve, 5000));
        }
    }
    return connection;
}

async function startConsumer() {
    const connection = await connectToRabbitMQ();
    const channel = await connection.createChannel();
    console.log('Channel created');

    await channel.assertQueue(QUEUE_NAME, { durable: true });
    console.log(`Queue ${QUEUE_NAME} is ready`);

    channel.consume(QUEUE_NAME, message => processMessage(message, channel), { noAck: false });
    console.log(`Waiting for messages in ${QUEUE_NAME}. To exit press CTRL+C`);
}

function processMessage(message, channel) {
    if (message !== null) {
        try {
            const content = message.content.toString();
            const reservationDetails = JSON.parse(content);
            console.log("Received:", reservationDetails);

            if (reservationDetails.status === PAYMENT_STATUSES.PAID) {
                console.log(`Triggering email to ${reservationDetails.email}`);
                // Simulate email sending
            }

            channel.ack(message);
        } catch (error) {
            console.error('Error processing message', error);
            channel.nack(message);
        }
    }
}

startConsumer();

module.exports = { startConsumer, connectToRabbitMQ, processMessage };
