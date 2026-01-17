# Pacheco Wedding Site!

WordPress site for our wedding!

## Technical details

1. Source the environment file (which you might need to create):

    ```shell
    source .env
    ```

2. Bring up the services:

    ```shell
    docker compose --progress=plain up --build --detach
    ```

3. (If initially creating the db) run the script to create a new user with all privileges:

    ```shell
    docker compose exec --no-tty db php < scripts/createuser.php
    ```

4. (If initially creating the db) run the `wp-cli` command to create the database:

    ```shell
    scripts/wp-cli db create
    ```

### Docker Permissions

You'll need to ensure ACLs are set. Run `scripts/ensure-acls`.
