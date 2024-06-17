const amqp = require('amqplib');

async function startConsumer() {
    const queueName = 'reservation_email_queue';
    const connectionString = 'amqp://localhost';

    try {
        const connection = await amqp.connect(connectionString);
        console.log('Connected to RabbitMQ');

        const channel = await connection.createChannel();
        console.log('Channel created');

        await channel.assertQueue(queueName, {
            durable: true
        });
        console.log(`Queue ${queueName} is ready`);

        console.log(`Waiting for messages in ${queueName}. To exit press CTRL+C`);
        await channel.consume(queueName, message => {
            if (message !== null) {
                const content = message.content.toString();
                const decoded = JSON.parse(content);
                console.log("Received:", decoded);

                // trigger email dispatch from here

                // ack message
                channel.ack(message);
            }
        });

    } catch (error) {
        console.error('Failed to connect or process messages:', error);
    }
}

startConsumer();
