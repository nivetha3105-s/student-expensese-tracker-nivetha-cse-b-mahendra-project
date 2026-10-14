# Student Expense Tracker

Web Technology project built with PHP, MySQL, HTML, CSS and JavaScript.

## Features
Register / login, add income or expense, edit, delete, search, filter by category,
monthly summary (SUM + CASE), spending by category (GROUP BY) and an all-months report.

## Folder structure
    expense-tracker/
    ├── index.php, login.php, logout.php
    ├── dashboard.php, add_transaction.php, edit_transaction.php, delete_transaction.php
    ├── report.php, test_db.php
    ├── config/     database.php (connection + BASE_URL), functions.php (helpers, header/footer)
    ├── css/        style.css
    ├── js/         app.js
    └── schema.sql

## Database tables
expense_users, transactions

## Setup (ByteXL, MySQL driver loaded from phpext)
Same pipeline as the internship portal. From `/home/workspace/my-project`:

    # 1) one-time: download the MySQL driver without root (skip if phpext/ already exists)
    mkdir -p phpext/apt/lists/partial phpext/apt/cache/archives/partial
    OPTS="-o Dir::State::Lists=$PWD/phpext/apt/lists -o Dir::Cache=$PWD/phpext/apt/cache -o APT::Sandbox::User=studio"
    apt-get $OPTS update
    cd phpext && apt-get $OPTS download php8.4-mysql && dpkg -x php8.4-mysql_*.deb . && cd ..

    # 2) one-time: create the tables in the ByteXL MySQL editor (run schema.sql)

    # 3) start the server
    EXT=$(dirname $(find $PWD/phpext -name pdo_mysql.so))
    php -d extension=$EXT/mysqlnd.so -d extension=$EXT/mysqli.so -d extension=$EXT/pdo_mysql.so -S 0.0.0.0:8080 -t expense-tracker

Open `https://<workspace-id>-8080.ws6.app/` (or `.../proxy/8080/` on the -5300 host).
`BASE_URL` is detected automatically in `config/database.php`.
Check the connection first at `/test_db.php`.
