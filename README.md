# Your assignment
The purpose of this test is to assess your Laravel skills, NodeJS skills and the ability to connect different microservice together. It also tests your ability to read specifications and solving problems. You have a **maximum of 4 hours** time.

### Evaluation Criteria
- NodeJS skills
- Laravel skills
- Problem solving skills
- Ability to read specifications
- Applying software best practices and industry-standard approaches
- Microservices understanding
- Security considerations and implementations

# Use Case
Parkos is moving to microservices and we want to separate our Reservation system and Email communication system into dedicated Microservices. The Reservation API is part of Laravel and dispatches events to the Email communication system (NodeJS) that will take care of sending emails. Your challenge is to replicate this set-up in Laravel and NodeJS.

## Reservation API (Laravel)
- Create an API that can do basic Create, Read and (CRU of CRUD) actions for parking reservations. It must be __RESTful__
- Reservations should contain at least the following properties:
  - Reservation code
  - Customer name
  - Customer email
  - Arrival date & time
  - Departure date & time
  - Payment status
- The API needs to send __asynchronous__ triggers that can be used by the Email communication service.
- You need to use at least the following technologies:
  - MySQL
  - Laravel (latest stable version)
  - RabbitMQ and/or Redis (to your preference)

## Email communication System (NodeJS / TypeScript)
- Build a small microservice
- Be sure it responds to the asynchronous triggers created by the Reservation API
- When the payment status transitions to 'paid', a mail should be triggered to the customer
- Instead of sending a real email, a simple console.log with the intention of sending mail suffices

## Bonus section
- OWASP Top 10
- Tests
- API documentation

# Getting started

This code repository includes a docker-compose YAML file. The docker-compose environment includes everything you need: webserver, PHP (with Laravel), RabbitMQ, Redis, Node and MySQL (8).

_You are not required to use our specific Docker set-up, please use our set-up for convenience or create your own Docker set-up._

## Useful hints and instructions

- Start the docker set-up by running `docker compose up -d`
- The node app runs with nodemon, so changes will be reloaded automatically
- If you want to run a command in the laravel container (like the required `composer install`), enter the terminal with `docker exec -it laravel /bin/bash` and locate the project files with `cd /var/www`.
- The Laravel app runs on [http://localhost/](http://localhost)
- The docker setup has an internal network where you can reach individual containers with there DNS service name (for example: db:3306, or redis:6379)
- Checkout the docker-compose.yml file for more details, like the mysql username and pass and service names

# ~ We wish you good luck with the assignment! ~