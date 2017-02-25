# A REST API theme for Roadiz CMS.

An implementation of `thephpleague/oauth2-server` v4.1.6 awesome component.

A bit of documentation: https://tools.ietf.org/html/rfc6749#page-45

**In huge WIP.**

## Entities to setup

Add these entity paths to your `conf/config.yml`:

```yaml
entities:
    - themes/RestApiTheme/Entities
    - themes/RestApiTheme/AbstractEntities
```

## Steps with User authentification

### Get authorization code

GET: `/oauth?client_id=CLIENT_ID&redirect_uri=REDIRECT_URL&response_type=code`

### Get access token 

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

## Steps without authentification

### Get access token 

POST: `/token` with data

| | |
| --- | --- |
| grant_type | `client_credentials` |
| client_id | CLIENT_ID | 
| client_secret | CLIENT_SECRET |
| scope | SCOPE | 

and header  `Content-Type: application/x-www-form-urlencoded` 
