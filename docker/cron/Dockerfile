FROM docker.io/bitnami/symfony:5.4.33

#Install Cron
RUN apt-get update && apt-get -y install cron

#Install Mariadb Client
RUN apt-get install -y mariadb-client

COPY docker/cron/crontab /etc/cron.d/crontab

RUN chmod 0644 /etc/cron.d/crontab

RUN crontab /etc/cron.d/crontab

RUN touch /var/log/cron.log

# Run the command on container startup
CMD cron && tail -f /var/log/cron.log
