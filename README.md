# A REST API theme for Roadiz CMS.

An implementation of `thephpleague/oauth2-server` v4.1.6 awesome component.

A bit of documentation: https://tools.ietf.org/html/rfc6749#page-45

## Install

Add these entity paths to your `conf/config.yml`:

```yaml
entities:
    - themes/RestApiTheme/Entities
    - themes/RestApiTheme/AbstractEntities
```

```shell
# Update dependencies
composer update --no-dev -o
# Install RestApiTheme into Roadiz
bin/roadiz themes:install /Themes/RestApiTheme/RestApiThemeApp
bin/roadiz themes:install --data /Themes/RestApiTheme/RestApiThemeApp
# Install all needed entities in database
bin/roadiz orm:schema-tool:update --dump-sql --force
# Clear Roadiz caches
bin/roadiz cache:clear
bin/roadiz cache:clear -e prod
```

## Steps with public user authentification
### Get authorization code from browser

- Visit `/oauth?client_id=CLIENT_ID&redirect_uri=REDIRECT_URL&response_type=code` in your browser.
- You should be redirected to a sign-in page
- After a successful sign-in, you will be redirected to the authorization page which will list all scopes requested by your API client
- Then after accepted, you will be redirected to your API client `redirect_uri` with your `code` in the query string. You can request an access-token now.

### Get access token with authorization code

POST: `/token` with data

| | |
| --- | --- |
| grant_type | `authorization_code` |
| client_id | CLIENT_ID | 
| client_secret | CLIENT_SECRET |
| scope | SCOPE | 
| redirect_uri | REDIRECT_URL | 
| code | *Code returned by authorization request* |

and header  `Content-Type: application/x-www-form-urlencoded` 

## Steps without user authentification 
### Get access token 

POST: `/token` with data

| | |
| --- | --- |
| grant_type | `client_credentials` |
| client_id | CLIENT_ID | 
| client_secret | CLIENT_SECRET |
| scope | SCOPE | 

and header  `Content-Type: application/x-www-form-urlencoded` 
