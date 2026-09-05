FROM php:8.1-apache

# Enable mod_rewrite for clean URLs
RUN a2enmod rewrite

# Install msmtp as sendmail replacement
RUN apt-get update \
 && apt-get install -y --no-install-recommends msmtp \
 && rm -rf /var/lib/apt/lists/* \
 && ln -sf /usr/bin/msmtp /usr/sbin/sendmail

RUN printf 'defaults\ntimeout 2\n\naccount default\nhost 127.0.0.1\nport 25\nfrom no-reply@nmc.edu.ph\n' \
 > /etc/msmtprc && chmod 644 /etc/msmtprc

# Copy PHP config — enforces disable_functions
COPY php.ini /usr/local/etc/php/php.ini

# Copy Apache vhost config
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy site source
COPY www/ /var/www/html/

# Copy .git into webroot — simulates a dev team deploying via `git clone`
# directly on the server without excluding the .git directory.
# This is the misconfiguration that exposes source history to attackers.
COPY git-history.tar.gz /tmp/
RUN tar xzf /tmp/git-history.tar.gz -C /var/www/html/ && rm /tmp/git-history.tar.gz

# uploads/ must be writable by www-data
RUN mkdir -p /var/www/html/uploads \
 && chown -R www-data:www-data /var/www/html/uploads \
 && chmod 755 /var/www/html/uploads

# Flag
RUN echo 'flag{funct10ns_1s_r3ally_g0od}' > /flag.txt && chmod 644 /flag.txt

EXPOSE 80
