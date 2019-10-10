local downstreamRepos = [
    "a-z-listing/proper-nouns",
];

local mysqlver = "5.6";
local matrix = [
    {
        wp: "5.0",
        php: ["7.0", "7.1", "7.2", "7.3"],
    },
    {
        wp: "5.1",
        php: ["7.0", "7.1", "7.2", "7.3"],
    },
    {
        wp: "5.2",
        php: ["7.0", "7.1", "7.2", "7.3"],
    },
    {
        wp: "latest",
        php: ["7.0", "7.1", "7.2", "7.3"],
    },
    {
        wp: "nightly",
        php: ["7.0", "7.1", "7.2", "7.3"],
    },
];

local services = [
	{
		name: "mysql",
		image: "mysql:" + mysqlver,
		environment: {
			"MYSQL_DATABASE": "wordpress_tests",
			"MYSQL_ROOT_PASSWORD": "mysql",
		},
	},
];

local TestStageTitle(php, wp) = "wordpress-" + wp + ":php-" + php;
local MatrixIterator(fn) = [
    fn(php, entry.wp)
    for entry in matrix
    for php in entry.php
];

local Pipeline(php, wp) = {
	kind: "pipeline",
	name: TestStageTitle(php, wp),

	steps: [
		{
			name: "phpunit",
			image: "bowlhat/gitlab-php-runner:" + php,
			commands: [
				"apt-get clean",
				"apt-get -yqq update",
				"DEBIAN_FRONTEND=noninteractive apt-get -yqqf install zip unzip subversion mariadb-client libmariadb-dev --fix-missing",
				"docker-php-ext-enable mbstring mysqli pdo_mysql intl gd zip bz2",
				"bash bin/install-wp-tests.sh wordpress_tests root mysql mysql " + wp + " true",
				"curl -sSLo phpunit.phar https://phar.phpunit.de/phpunit-5.phar && chmod +x phpunit.phar",
				"composer install",
				"./phpunit.phar",
			],
		},
	],
	services: services,
};

local triggerSteps = [
    {
        name: "trigger",
        image: "plugins/downstream",
        settings: {
            server: "https://drone.bowlhat.net",
            token: {
                from_secret: "drone_token",
            },
            fork: true,
            repositories: downstreamRepos,
        },
    },
];

MatrixIterator(Pipeline)+[{
    kind: "pipeline",
    name: "trigger",

    steps: triggerSteps,
    trigger: {
        status: ["success"],
        branch: ["master"],
    },
    depends_on: MatrixIterator(TestStageTitle)
}]
