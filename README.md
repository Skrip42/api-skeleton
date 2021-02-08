# api-skeleton
symfony api skeleton
## Creating new project
```bash
composer create-project skrip42/api-skeleton <project_name> 
```
create .env.local and and add environment variable definitions to it:
- API_KEY - you api authentification key
- LEK_URL - you logstash host
- LEK_SYSLOG_PORT - port on which logstash listens syslog
- LEK_SERVER_NAME - you project name for logstash
