HOST ?= rose
DIR ?= /var/www/rose/htdocs/chatbot
upload:
	rsync -av --exclude .git . ${HOST}:${DIR}/

