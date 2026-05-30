# Pacheco Wedding Site!

WordPress site for our wedding!

## Technical details

1. Source the environment file (which you might need to create):

    ```shell
    source .env
    ```

2. Optionally bring down the existing services if starting from scratch:

    ```shell
    docker compose down --volumes
    ```

3. Bring up the services:

    ```shell
    docker compose --progress plain up --detach --build --pull always
    ```

### Docker Permissions

You'll need to ensure ACLs are set. Run `scripts/ensure-acls`.
