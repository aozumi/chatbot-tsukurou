HOST ?= rose
DIR ?= /var/www/rose/htdocs/chatbot
upload:
	rsync -av --delete --exclude .git . ${HOST}:${DIR}/

