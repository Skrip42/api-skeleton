# api-skeleton
symfony api skeleton
## Creating new project
```bash
composer create-project skrip42/api-skeleton <project_name> 
```
create .env.local and and add environment variable definitions to it:
- API_KEY - you api authentification key


## Enabling LEK integration
- uncomment import lek_integration.yam in you services.yaml
- uncomment lek handler in you monolog.yaml
- add you lek authorisation data in .env file
