BANST_AWS_CLI = "banst/awscli"
DRONE_CLI = "drone/cli:alpine"
MAILHOG_MAILHOG = "mailhog/mailhog"
MINIO_MC = "minio/mc:RELEASE.2020-12-18T10-53-53Z"
OC_CI_ALPINE = "owncloudci/alpine:latest"
OC_CI_BAZEL_BUILDIFIER = "owncloudci/bazel-buildifier"
OC_CI_CEPH = "owncloudci/ceph:tag-build-master-jewel-ubuntu-16.04"
OC_CI_CORE = "owncloudci/core"
OC_CI_DRONE_CANCEL_PREVIOUS_BUILDS = "owncloudci/drone-cancel-previous-builds"
OC_CI_DRONE_SKIP_PIPELINE = "owncloudci/drone-skip-pipeline"
OC_CI_NODEJS = "owncloudci/nodejs:%s"
OC_CI_ORACLE_XE = "owncloudci/oracle-xe:latest"
OC_CI_PHP = "owncloudci/php:%s"
OC_CI_SCALITY_S3_SERVER = "owncloudci/scality-s3server"
OC_CI_WAIT_FOR = "owncloudci/wait-for:latest"
OC_OPS_ELASTIC_SEARCH = "owncloudops/elasticsearch:%s"
OC_UBUNTU = "owncloud/ubuntu:20.04"
OSIXIA_OPEN_LDAP = "osixia/openldap"
PLUGINS_GITHUB_RELEASE = "plugins/github-release"
PLUGINS_S3 = "plugins/s3"
PLUGINS_S3_CACHE = "plugins/s3-cache:1"
PLUGINS_SLACK = "plugins/slack:1"
SELENIUM_STANDALONE_CHROME_DEBUG = "selenium/standalone-chrome-debug:3.141.59-oxygen"
SELENIUM_STANDALONE_FIREFOX_DEBUG = "selenium/standalone-firefox-debug:3.8.1"
SONARSOURCE_SONAR_SCANNER_CLI = "sonarsource/sonar-scanner-cli"
THEGEEKLAB_DRONE_GITHUB_COMMENT = "thegeeklab/drone-github-comment:1"

DEFAULT_PHP_VERSION = "7.4"
DEFAULT_NODEJS_VERSION = "14"

dir = {
    "base": "/var/www/owncloud",
    "federated": "/var/www/owncloud/federated",
    "server": "/var/www/owncloud/server",
    "testrunner": "/var/www/owncloud/testrunner",
    "scalityConfig": "/var/www/owncloud/server/config/scality.config.php",
    "browserService": "/home/seluser/Downloads",
}

config = {
    "rocketchat": {
        "channel": "builds",
        "from_secret": "private_rocketchat",
    },
    "branches": [
        "master",
    ],
    "appInstallCommandPhp": "make vendor",
    "codestyle": True,
    "phpstan": True,
    "javascript": False,
    "phpunit": True,
    "acceptance": {
        "webUI": {
            "suites": {
                "webUITwoFactorTOTP": "webUITwoFactTOTP",
            },
            "browsers": [
                "chrome",
                "firefox",
            ],
        },
        # Note: the API and CLI tests need webUI steps for their setup, so they look like webUI suites
        "webUIother": {
            "suites": {
                "webUIapiTwoFactorTOTP": "webUIapiTOTP",
                "webUIcliTwoFactorTOTP": "webUIcliTOTP",
            },
        },
        "webUI-encryption": {
            "suites": {
                "webUIapiTwoFactorTOTP": "webUIapiTOTPEnc",
                "webUIcliTwoFactorTOTP": "webUIcliTOTPEnc",
                "webUITwoFactorTOTP": "webUI2FactTOTPEnc",
            },
            "extraApps": {
                "encryption": "",
            },
            "extraSetup": [
                {
                    "name": "configure-encryption",
                    "image": OC_CI_PHP % DEFAULT_PHP_VERSION,
                    "commands": [
                        "cd %s" % dir["server"],
                        "php occ encryption:enable",
                        "php occ encryption:select-encryption-type masterkey --yes",
                        "php occ config:list",
                    ],
                },
            ],
            "servers": [
                "daily-master-qa",
            ],
        },
        "webUI-guests": {
            "suites": [
                "webUITOTPGuests",
            ],
            "extraApps": {
                "guests": "",
            },
            "servers": [
                "daily-master-qa",
            ],
            "emailNeeded": True,
        },
    },
}

def main(ctx):
    before = beforePipelines(ctx)

    coverageTests = coveragePipelines(ctx)
    if (coverageTests == False):
        print("Errors detected in coveragePipelines. Review messages above.")
        return []

    dependsOn(before, coverageTests)

    nonCoverageTests = nonCoveragePipelines(ctx)
    if (nonCoverageTests == False):
        print("Errors detected in nonCoveragePipelines. Review messages above.")
        return []

    dependsOn(before, nonCoverageTests)

    stages = stagePipelines(ctx)
    if (stages == False):
        print("Errors detected. Review messages above.")
        return []

    dependsOn(before, stages)

    if (coverageTests == []):
        afterCoverageTests = []
    else:
        afterCoverageTests = afterCoveragePipelines(ctx)
        dependsOn(coverageTests, afterCoverageTests)

    after = afterPipelines(ctx)
    dependsOn(afterCoverageTests + nonCoverageTests + stages, after)

    return before + coverageTests + afterCoverageTests + nonCoverageTests + stages + after

def beforePipelines(ctx):
    return codestyle(ctx) + jscodestyle(ctx) + cancelPreviousBuilds() + phpstan(ctx) + phan(ctx) + phplint(ctx) + checkStarlark()

def coveragePipelines(ctx):
    # All unit test pipelines that have coverage or other test analysis reported
    jsPipelines = javascript(ctx, True)
    phpUnitPipelines = phpTests(ctx, "phpunit", True)
    phpIntegrationPipelines = phpTests(ctx, "phpintegration", True)
    if (jsPipelines == False) or (phpUnitPipelines == False) or (phpIntegrationPipelines == False):
        return False

    return jsPipelines + phpUnitPipelines + phpIntegrationPipelines

def nonCoveragePipelines(ctx):
    # All unit test pipelines that do not have coverage or other test analysis reported
    jsPipelines = javascript(ctx, False)
    phpUnitPipelines = phpTests(ctx, "phpunit", False)
    phpIntegrationPipelines = phpTests(ctx, "phpintegration", False)
    if (jsPipelines == False) or (phpUnitPipelines == False) or (phpIntegrationPipelines == False):
        return False

    return jsPipelines + phpUnitPipelines + phpIntegrationPipelines

def stagePipelines(ctx):
    buildPipelines = build(ctx)
    acceptancePipelines = acceptance(ctx)
    if (buildPipelines == False) or (acceptancePipelines == False):
        return False

    return buildPipelines + acceptancePipelines

def afterCoveragePipelines(ctx):
    return [
        sonarAnalysis(ctx),
    ]

def afterPipelines(ctx):
    return [
        notify(),
    ]

def codestyle(ctx):
    pipelines = []

    if "codestyle" not in config:
        return pipelines

    default = {
        "phpVersions": [DEFAULT_PHP_VERSION],
    }

    if "defaults" in config:
        if "codestyle" in config["defaults"]:
            for item in config["defaults"]["codestyle"]:
                default[item] = config["defaults"]["codestyle"][item]

    codestyleConfig = config["codestyle"]

    if type(codestyleConfig) == "bool":
        if codestyleConfig:
            # the config has "codestyle" true, so specify an empty dict that will get the defaults
            codestyleConfig = {}
        else:
            return pipelines

    if len(codestyleConfig) == 0:
        # "codestyle" is an empty dict, so specify a single section that will get the defaults
        codestyleConfig = {"doDefault": {}}

    for category, matrix in codestyleConfig.items():
        params = {}
        for item in default:
            params[item] = matrix[item] if item in matrix else default[item]

        for phpVersion in params["phpVersions"]:
            name = "coding-standard-php%s" % phpVersion

            result = {
                "kind": "pipeline",
                "type": "docker",
                "name": name,
                "workspace": {
                    "base": dir["base"],
                    "path": "server/apps/%s" % ctx.repo.name,
                },
                "steps": skipIfUnchanged(ctx, "lint") +
                         [
                             {
                                 "name": "coding-standard",
                                 "image": OC_CI_PHP % phpVersion,
                                 "commands": [
                                     "make test-php-style",
                                 ],
                             },
                         ],
                "depends_on": [],
                "trigger": {
                    "ref": [
                        "refs/pull/**",
                        "refs/tags/**",
                    ],
                },
            }

            for branch in config["branches"]:
                result["trigger"]["ref"].append("refs/heads/%s" % branch)

            pipelines.append(result)

    return pipelines

def jscodestyle(ctx):
    pipelines = []

    if "jscodestyle" not in config:
        return pipelines

    if type(config["jscodestyle"]) == "bool":
        if not config["jscodestyle"]:
            return pipelines

    result = {
        "kind": "pipeline",
        "type": "docker",
        "name": "coding-standard-js",
        "workspace": {
            "base": dir["base"],
            "path": "server/apps/%s" % ctx.repo.name,
        },
        "steps": skipIfUnchanged(ctx, "lint") +
                 [
                     {
                         "name": "coding-standard-js",
                         "image": OC_CI_NODEJS % DEFAULT_NODEJS_VERSION,
                         "commands": [
                             "make test-js-style",
                         ],
                     },
                 ],
        "depends_on": [],
        "trigger": {
            "ref": [
                "refs/pull/**",
                "refs/tags/**",
            ],
        },
    }

    for branch in config["branches"]:
        result["trigger"]["ref"].append("refs/heads/%s" % branch)

    pipelines.append(result)

    return pipelines

def cancelPreviousBuilds():
    return [{
        "kind": "pipeline",
        "type": "docker",
        "name": "cancel-previous-builds",
        "clone": {
            "disable": True,
        },
        "steps": [{
            "name": "cancel-previous-builds",
            "image": OC_CI_DRONE_CANCEL_PREVIOUS_BUILDS,
            "settings": {
                "DRONE_TOKEN": {
                    "from_secret": "drone_token",
                },
            },
        }],
        "depends_on": [],
        "trigger": {
            "ref": [
                "refs/pull/**",
            ],
        },
    }]

def phpstan(ctx):
    pipelines = []

    if "phpstan" not in config:
        return pipelines

    default = {
        "phpVersions": [DEFAULT_PHP_VERSION],
        "logLevel": "2",
        "extraApps": {},
        "enableApp": True,
    }

    if "defaults" in config:
        if "phpstan" in config["defaults"]:
            for item in config["defaults"]["phpstan"]:
                default[item] = config["defaults"]["phpstan"][item]

    phpstanConfig = config["phpstan"]

    if type(phpstanConfig) == "bool":
        if phpstanConfig:
            # the config has "phpstan" true, so specify an empty dict that will get the defaults
            phpstanConfig = {}
        else:
            return pipelines

    if len(phpstanConfig) == 0:
        # "phpstan" is an empty dict, so specify a single section that will get the defaults
        phpstanConfig = {"doDefault": {}}

    for category, matrix in phpstanConfig.items():
        params = {}
        for item in default:
            params[item] = matrix[item] if item in matrix else default[item]

        for phpVersion in params["phpVersions"]:
            name = "phpstan-php%s" % phpVersion

            result = {
                "kind": "pipeline",
                "type": "docker",
                "name": name,
                "workspace": {
                    "base": dir["base"],
                    "path": "server/apps/%s" % ctx.repo.name,
                },
                "steps": skipIfUnchanged(ctx, "lint") +
                         installCore(ctx, "daily-master-qa", "sqlite", False) +
                         installAppPhp(ctx, phpVersion) +
                         installExtraApps(phpVersion, params["extraApps"]) +
                         setupServerAndApp(ctx, phpVersion, params["logLevel"], False, params["enableApp"]) +
                         [
                             {
                                 "name": "phpstan",
                                 "image": OC_CI_PHP % phpVersion,
                                 "commands": [
                                     "make test-php-phpstan",
                                 ],
                             },
                         ],
                "depends_on": [],
                "trigger": {
                    "ref": [
                        "refs/pull/**",
                        "refs/tags/**",
                    ],
                },
            }

            for branch in config["branches"]:
                result["trigger"]["ref"].append("refs/heads/%s" % branch)

            pipelines.append(result)

    return pipelines

def phan(ctx):
    pipelines = []

    if "phan" not in config:
        return pipelines

    default = {
        "phpVersions": [DEFAULT_PHP_VERSION],
    }

    if "defaults" in config:
        if "phan" in config["defaults"]:
            for item in config["defaults"]["phan"]:
                default[item] = config["defaults"]["phan"][item]

    phanConfig = config["phan"]

    if type(phanConfig) == "bool":
        if phanConfig:
            # the config has "phan" true, so specify an empty dict that will get the defaults
            phanConfig = {}
        else:
            return pipelines

    if len(phanConfig) == 0:
        # "phan" is an empty dict, so specify a single section that will get the defaults
        phanConfig = {"doDefault": {}}

    for category, matrix in phanConfig.items():
        params = {}
        for item in default:
            params[item] = matrix[item] if item in matrix else default[item]

        for phpVersion in params["phpVersions"]:
            name = "phan-php%s" % phpVersion

            result = {
                "kind": "pipeline",
                "type": "docker",
                "name": name,
                "workspace": {
                    "base": dir["base"],
                    "path": "server/apps/%s" % ctx.repo.name,
                },
                "steps": skipIfUnchanged(ctx, "lint") +
                         installCore(ctx, "daily-master-qa", "sqlite", False) +
                         [
                             {
                                 "name": "phan",
                                 "image": OC_CI_PHP % phpVersion,
                                 "commands": [
                                     "make test-php-phan",
                                 ],
                             },
                         ],
                "depends_on": [],
                "trigger": {
                    "ref": [
                        "refs/pull/**",
                        "refs/tags/**",
                    ],
                },
            }

            for branch in config["branches"]:
                result["trigger"]["ref"].append("refs/heads/%s" % branch)

            pipelines.append(result)

    return pipelines

def build(ctx):
    pipelines = []

    if "build" not in config:
        return pipelines

    default = {
        "phpVersions": [DEFAULT_PHP_VERSION],
        "commands": [
            "make dist",
        ],
        "extraEnvironment": {},
        "configureTarOnTag": False,
    }

    if "defaults" in config:
        if "build" in config["defaults"]:
            for item in config["defaults"]["build"]:
                default[item] = config["defaults"]["build"][item]

    matrix = config["build"]

    if type(matrix) == "bool":
        if matrix:
            # the config has "build" true, so specify an empty dict that will get the defaults
            matrix = {}
        else:
            return pipelines

    params = {}
    for item in default:
        params[item] = matrix[item] if item in matrix else default[item]

    for phpVersion in params["phpVersions"]:
        result = {
            "kind": "pipeline",
            "type": "docker",
            "name": "build",
            "workspace": {
                "base": dir["base"],
                "path": "server/apps/%s" % ctx.repo.name,
            },
            "steps": [
                {
                    "name": "build",
                    "image": OC_CI_PHP % phpVersion,
                    "environment": params["extraEnvironment"],
                    "commands": params["commands"],
                },
            ] + ([
                {
                    "name": "github_release",
                    "image": PLUGINS_GITHUB_RELEASE,
                    "settings": {
                        "checksum": "sha256",
                        "file_exists": "overwrite",
                        "files": "build/dist/%s.tar.gz" % ctx.repo.name,
                        "prerelease": True,
                    },
                    "environment": {
                        "GITHUB_TOKEN": {
                            "from_secret": "github_token",
                        },
                    },
                    "when": {
                        "event": [
                            "tag",
                        ],
                    },
                },
            ] if params["configureTarOnTag"] else []),
            "depends_on": [],
            "trigger": {
                "ref": [
                    "refs/pull/**",
                    "refs/tags/**",
                ],
            },
        }

        for branch in config["branches"]:
            result["trigger"]["ref"].append("refs/heads/%s" % branch)

        pipelines.append(result)

    return pipelines

def javascript(ctx, withCoverage):
    pipelines = []

    if "javascript" not in config:
        return pipelines

    default = {
        "coverage": False,
        "logLevel": "2",
        "extraSetup": [],
        "extraServices": [],
        "extraEnvironment": {},
        "extraCommandsBeforeTestRun": [],
        "extraTeardown": [],
        "skip": False,
        "enableApp": True,
    }

    if "defaults" in config:
        if "javascript" in config["defaults"]:
            for item in config["defaults"]["javascript"]:
                default[item] = config["defaults"]["javascript"][item]

    matrix = config["javascript"]

    if type(matrix) == "bool":
        if matrix:
            # the config has "javascript" true, so specify an empty dict that will get the defaults
            matrix = {}
        else:
            return pipelines

    params = {}
    for item in default:
        params[item] = matrix[item] if item in matrix else default[item]

    if params["skip"]:
        return pipelines

    # if we only want pipelines with coverage, and this pipeline does not do coverage, then do not include it
    if withCoverage and not params["coverage"]:
        return pipelines

    # if we only want pipelines without coverage, and this pipeline does coverage, then do not include it
    if not withCoverage and params["coverage"]:
        return pipelines

    result = {
        "kind": "pipeline",
        "type": "docker",
        "name": "javascript-tests",
        "workspace": {
            "base": dir["base"],
            "path": "server/apps/%s" % ctx.repo.name,
        },
        "steps": skipIfUnchanged(ctx, "unit-tests") +
                 installCore(ctx, "daily-master-qa", "sqlite", False) +
                 installAppJavaScript(ctx) +
                 setupServerAndApp(ctx, DEFAULT_PHP_VERSION, params["logLevel"], False, params["enableApp"]) +
                 params["extraSetup"] +
                 [
                     {
                         "name": "js-tests",
                         "image": OC_CI_NODEJS % getNodeJsVersion(),
                         "environment": params["extraEnvironment"],
                         "commands": params["extraCommandsBeforeTestRun"] + [
                             "make test-js",
                         ],
                     },
                 ] + params["extraTeardown"],
        "services": params["extraServices"],
        "depends_on": [],
        "trigger": {
            "ref": [
                "refs/pull/**",
                "refs/tags/**",
            ],
        },
    }

    if params["coverage"]:
        result["steps"].append({
            "name": "coverage-cache",
            "image": PLUGINS_S3,
            "settings": {
                "endpoint": {
                    "from_secret": "cache_s3_endpoint",
                },
                "bucket": "cache",
                "source": "./coverage/lcov.info",
                "target": "%s/%s" % (ctx.repo.slug, ctx.build.commit + "-${DRONE_BUILD_NUMBER}"),
                "path_style": True,
                "strip_prefix": "./coverage",
                "access_key": {
                    "from_secret": "cache_s3_access_key",
                },
                "secret_key": {
                    "from_secret": "cache_s3_secret_key",
                },
            },
        })

    for branch in config["branches"]:
        result["trigger"]["ref"].append("refs/heads/%s" % branch)

    return [result]

def phpTests(ctx, testType, withCoverage):
    pipelines = []

    if testType not in config:
        return pipelines

    errorFound = False

    # The default PHP unit test settings for a PR.
    # Note: do not run Oracle by default in PRs.
    prDefault = {
        "phpVersions": [DEFAULT_PHP_VERSION],
        "servers": ["daily-master-qa"],
        "databases": [
            "sqlite",
            "mariadb:10.2",
            "mysql:8.0",
            "postgres:9.4",
        ],
        "coverage": True,
        "includeKeyInMatrixName": False,
        "logLevel": "2",
        "cephS3": False,
        "scalityS3": False,
        "extraSetup": [],
        "extraServices": [],
        "extraEnvironment": {},
        "extraCommandsBeforeTestRun": [],
        "extraApps": {},
        "extraTeardown": [],
        "skip": False,
        "enableApp": True,
    }

    # The default PHP unit test settings for the cron job (usually runs nightly).
    cronDefault = {
        "phpVersions": [DEFAULT_PHP_VERSION],
        "servers": ["daily-master-qa"],
        "databases": [
            "sqlite",
            "mariadb:10.2",
            "mysql:8.0",
            "postgres:9.4",
            "oracle",
        ],
        "coverage": True,
        "includeKeyInMatrixName": False,
        "logLevel": "2",
        "cephS3": False,
        "scalityS3": False,
        "extraSetup": [],
        "extraServices": [],
        "extraEnvironment": {},
        "extraCommandsBeforeTestRun": [],
        "extraApps": {},
        "extraTeardown": [],
        "skip": False,
        "enableApp": True,
    }

    if (ctx.build.event == "cron"):
        default = cronDefault
    else:
        default = prDefault

    if "defaults" in config:
        if testType in config["defaults"]:
            for item in config["defaults"][testType]:
                default[item] = config["defaults"][testType][item]

    phpTestConfig = config[testType]

    if type(phpTestConfig) == "bool":
        if phpTestConfig:
            # the config has just True, so specify an empty dict that will get the defaults
            phpTestConfig = {}
        else:
            return pipelines

    if len(phpTestConfig) == 0:
        # the PHP test config is an empty dict, so specify a single section that will get the defaults
        phpTestConfig = {"doDefault": {}}

    for category, matrix in phpTestConfig.items():
        params = {}
        for item in default:
            params[item] = matrix[item] if item in matrix else default[item]

        if params["skip"]:
            continue

        # if we only want pipelines with coverage, and this pipeline does not do coverage, then do not include it
        if withCoverage and not params["coverage"]:
            continue

        # if we only want pipelines without coverage, and this pipeline does coverage, then do not include it
        if not withCoverage and params["coverage"]:
            continue

        cephS3Params = params["cephS3"]
        if type(cephS3Params) == "bool":
            cephS3Needed = cephS3Params
            filesPrimaryS3NeededForCeph = cephS3Params
        else:
            cephS3Needed = True
            filesPrimaryS3NeededForCeph = cephS3Params["filesPrimaryS3Needed"] if "filesPrimaryS3Needed" in cephS3Params else True

        scalityS3Params = params["scalityS3"]
        if type(scalityS3Params) == "bool":
            scalityS3Needed = scalityS3Params
            filesPrimaryS3NeededForScality = scalityS3Params
        else:
            scalityS3Needed = True
            filesPrimaryS3NeededForScality = scalityS3Params["filesPrimaryS3Needed"] if "filesPrimaryS3Needed" in scalityS3Params else True

        if ((ctx.repo.name != "files_primary_s3") and (filesPrimaryS3NeededForCeph or filesPrimaryS3NeededForScality)):
            # If we are not already "files_primary_s3" and we need S3 storage, then install the "files_primary_s3" app
            extraAppsDict = {
                "files_primary_s3": "composer install",
            }
            for app, command in params["extraApps"].items():
                extraAppsDict[app] = command
            params["extraApps"] = extraAppsDict

        for phpVersion in params["phpVersions"]:
            if testType == "phpunit":
                if params["coverage"]:
                    command = "make test-php-unit-dbg"
                else:
                    command = "make test-php-unit"
            elif params["coverage"]:
                command = "make test-php-integration-dbg"
            else:
                command = "make test-php-integration"

            # Get the first 3 characters of the PHP version (7.4 or 8.0 etc)
            # And use that for constructing the pipeline name
            # That helps shorten pipeline names when using owncloud-ci images
            # that have longer names like 7.4-ubuntu20.04
            phpVersionForPipelineName = phpVersion[0:3]

            for server in params["servers"]:
                for db in params["databases"]:
                    keyString = "-" + category if params["includeKeyInMatrixName"] else ""
                    if len(params["servers"]) > 1:
                        serverString = "-%s" % server.replace("daily-", "").replace("-qa", "")
                    else:
                        serverString = ""
                    name = "%s%s-php%s%s-%s" % (testType, keyString, phpVersionForPipelineName, serverString, db.replace(":", ""))
                    maxLength = 50
                    nameLength = len(name)
                    if nameLength > maxLength:
                        print("Error: generated phpunit stage name of length", nameLength, "is not supported. The maximum length is " + str(maxLength) + ".", name)
                        errorFound = True

                    result = {
                        "kind": "pipeline",
                        "type": "docker",
                        "name": name,
                        "workspace": {
                            "base": dir["base"],
                            "path": "server/apps/%s" % ctx.repo.name,
                        },
                        "steps": skipIfUnchanged(ctx, "unit-tests") +
                                 installCore(ctx, server, db, False) +
                                 installAppPhp(ctx, phpVersion) +
                                 installExtraApps(phpVersion, params["extraApps"]) +
                                 setupServerAndApp(ctx, phpVersion, params["logLevel"], False, params["enableApp"]) +
                                 setupCeph(params["cephS3"]) +
                                 setupScality(params["scalityS3"]) +
                                 params["extraSetup"] +
                                 [
                                     {
                                         "name": "%s-tests" % testType,
                                         "image": OC_CI_PHP % phpVersion,
                                         "environment": params["extraEnvironment"],
                                         "commands": params["extraCommandsBeforeTestRun"] + [
                                             command,
                                         ],
                                     },
                                 ] + params["extraTeardown"],
                        "services": databaseService(db) +
                                    cephService(params["cephS3"]) +
                                    scalityService(params["scalityS3"]) +
                                    params["extraServices"],
                        "depends_on": [],
                        "trigger": {
                            "ref": [
                                "refs/pull/**",
                                "refs/tags/**",
                            ],
                        },
                    }

                    if params["coverage"]:
                        result["steps"].append({
                            "name": "coverage-rename",
                            "image": OC_CI_PHP % phpVersion,
                            "commands": [
                                "mv tests/output/clover.xml tests/output/clover-%s.xml" % (name),
                            ],
                        })
                        result["steps"].append({
                            "name": "coverage-cache-1",
                            "image": PLUGINS_S3,
                            "settings": {
                                "endpoint": {
                                    "from_secret": "cache_s3_endpoint",
                                },
                                "bucket": "cache",
                                "source": "tests/output/clover-%s.xml" % (name),
                                "target": "%s/%s" % (ctx.repo.slug, ctx.build.commit + "-${DRONE_BUILD_NUMBER}"),
                                "path_style": True,
                                "strip_prefix": "tests/output",
                                "access_key": {
                                    "from_secret": "cache_s3_access_key",
                                },
                                "secret_key": {
                                    "from_secret": "cache_s3_secret_key",
                                },
                            },
                        })

                    for branch in config["branches"]:
                        result["trigger"]["ref"].append("refs/heads/%s" % branch)

                    pipelines.append(result)

    if errorFound:
        return False

    return pipelines

def acceptance(ctx):
    pipelines = []

    if "acceptance" not in config:
        return pipelines

    if type(config["acceptance"]) == "bool":
        if not config["acceptance"]:
            return pipelines

    errorFound = False

    default = {
        "servers": ["daily-master-qa", "latest"],
        "browsers": ["chrome"],
        "phpVersions": [DEFAULT_PHP_VERSION],
        "databases": ["mariadb:10.2"],
        "esVersions": ["none"],
        "federatedServerNeeded": False,
        "filterTags": "",
        "logLevel": "2",
        "emailNeeded": False,
        "ldapNeeded": False,
        "cephS3": False,
        "scalityS3": False,
        "ssl": False,
        "xForwardedFor": False,
        "extraSetup": [],
        "extraServices": [],
        "extraTeardown": [],
        "extraEnvironment": {},
        "extraCommandsBeforeTestRun": [],
        "extraApps": {},
        "externalScality": [],
        "useBundledApp": False,
        "includeKeyInMatrixName": False,
        "runAllSuites": False,
        "runCoreTests": False,
        "numberOfParts": 1,
        "cron": "",
        "pullRequestAndCron": "nightly",
        "skip": False,
        "debugSuites": [],
        "skipExceptParts": [],
        "earlyFail": True,
        "enableApp": True,
        "selUserNeeded": False,
    }

    if "defaults" in config:
        if "acceptance" in config["defaults"]:
            for item in config["defaults"]["acceptance"]:
                default[item] = config["defaults"]["acceptance"][item]

    for category, matrix in config["acceptance"].items():
        if type(matrix["suites"]) == "list":
            suites = {}
            for suite in matrix["suites"]:
                suites[suite] = suite
        else:
            suites = matrix["suites"]

        if "debugSuites" in matrix and len(matrix["debugSuites"]) != 0:
            if type(matrix["debugSuites"]) == "list":
                suites = {}
                for suite in matrix["debugSuites"]:
                    suites[suite] = suite
            else:
                suites = matrix["debugSuites"]

        for suite, alternateSuiteName in suites.items():
            isWebUI = suite.startswith("webUI")
            isAPI = suite.startswith("api")
            isCLI = suite.startswith("cli")

            params = {}
            for item in default:
                params[item] = matrix[item] if item in matrix else default[item]

            if params["skip"]:
                continue

            # switch off earlyFail if the PR title contains full-ci
            if ("full-ci" in ctx.build.title.lower()):
                params["earlyFail"] = False

            # switch off earlyFail when running cron builds (for example, nightly CI)
            if (ctx.build.event == "cron"):
                params["earlyFail"] = False

            if "externalScality" in params and len(params["externalScality"]) != 0:
                # We want to use an external scality server for this pipeline.
                # That uses some "standard" extraSetup and extraTeardown.
                # Put the needed setup and teardown in place.
                params["extraSetup"] = [
                    {
                        "name": "configure-app",
                        "image": OC_CI_PHP % DEFAULT_PHP_VERSION,
                        "commands": [
                            "cd %s/apps/files_primary_s3" % dir["server"],
                            "cp tests/drone/scality.config.php %s/config" % dir["server"],
                            "sed -i -e \"s/owncloud/owncloud-acceptance-tests-$DRONE_BUILD_NUMBER-$DRONE_STAGE_NUMBER/\" %s" % dir["scalityConfig"],
                            "sed -i -e \"s/accessKey1/$SCALITY_KEY/\" %s" % dir["scalityConfig"],
                            "sed -i -e \"s/verySecretKey1/$SCALITY_SECRET_ESCAPED/\" %s" % dir["scalityConfig"],
                            "sed -i -e \"s/http/https/\" %s" % dir["scalityConfig"],
                            "sed -i -e \"s/scality:8000/%s/\" %s" % (params["externalScality"]["externalServerUrl"], dir["scalityConfig"]),
                            "cd %s" % dir["server"],
                            "php occ s3:create-bucket owncloud-acceptance-tests-$DRONE_BUILD_NUMBER-$DRONE_STAGE_NUMBER --accept-warning",
                            "cd %s/apps/files_primary_s3" % dir["testrunner"],
                        ],
                        "environment": {
                            "SCALITY_KEY": {
                                "from_secret": params["externalScality"]["secrets"]["scality_key"],
                            },
                            "SCALITY_SECRET": {
                                "from_secret": params["externalScality"]["secrets"]["scality_secret"],
                            },
                            "SCALITY_SECRET_ESCAPED": {
                                "from_secret": params["externalScality"]["secrets"]["scality_secret_escaped"],
                            },
                        },
                    },
                ]
                params["extraTeardown"] = [
                    {
                        "name": "cleanup-scality-bucket",
                        "image": BANST_AWS_CLI,
                        "failure": "ignore",
                        "commands": [
                            "aws configure set aws_access_key_id $SCALITY_KEY",
                            "aws configure set aws_secret_access_key $SCALITY_SECRET",
                            "aws --endpoint-url $SCALITY_ENDPOINT s3 rm --recursive s3://owncloud-acceptance-tests-$DRONE_BUILD_NUMBER-$DRONE_STAGE_NUMBER",
                            "%s/apps/files_primary_s3/tests/delete_all_object_versions.sh $SCALITY_ENDPOINT owncloud-acceptance-tests-$DRONE_BUILD_NUMBER-$DRONE_STAGE_NUMBER" % dir["testrunner"],
                            "aws --endpoint-url $SCALITY_ENDPOINT s3 rb --force s3://owncloud-acceptance-tests-$DRONE_BUILD_NUMBER-$DRONE_STAGE_NUMBER",
                        ],
                        "environment": {
                            "SCALITY_KEY": {
                                "from_secret": params["externalScality"]["secrets"]["scality_key"],
                            },
                            "SCALITY_SECRET": {
                                "from_secret": params["externalScality"]["secrets"]["scality_secret"],
                            },
                            "SCALITY_ENDPOINT": "https://%s" % params["externalScality"]["externalServerUrl"],
                        },
                        "when": {
                            "status": [
                                "failure",
                                "success",
                            ],
                        },
                    },
                ]

            if isAPI or isCLI:
                params["browsers"] = [""]

            cephS3Params = params["cephS3"]
            if type(cephS3Params) == "bool":
                cephS3Needed = cephS3Params
                filesPrimaryS3NeededForCeph = cephS3Params
            else:
                cephS3Needed = True
                filesPrimaryS3NeededForCeph = cephS3Params["filesPrimaryS3Needed"] if "filesPrimaryS3Needed" in cephS3Params else True

            scalityS3Params = params["scalityS3"]
            if type(scalityS3Params) == "bool":
                scalityS3Needed = scalityS3Params
                filesPrimaryS3NeededForScality = scalityS3Params
            else:
                scalityS3Needed = True
                filesPrimaryS3NeededForScality = scalityS3Params["filesPrimaryS3Needed"] if "filesPrimaryS3Needed" in scalityS3Params else True

            if ((ctx.repo.name != "files_primary_s3") and (filesPrimaryS3NeededForCeph or filesPrimaryS3NeededForScality)):
                # If we are not already "files_primary_s3" and we need S3 object storage, then install the "files_primary_s3" app
                extraAppsDict = {
                    "files_primary_s3": "composer install",
                }
                for app, command in params["extraApps"].items():
                    extraAppsDict[app] = command
                params["extraApps"] = extraAppsDict

            for testConfig in buildTestConfig(params):
                debugPartsEnabled = (len(testConfig["skipExceptParts"]) != 0)
                if debugPartsEnabled and testConfig["runPart"] not in testConfig["skipExceptParts"]:
                    continue

                name = "unknown"
                phpVersionForDocker = testConfig["phpVersion"]

                # Get the first 3 characters of the PHP version (7.4 or 8.0 etc)
                # And use that for constructing the pipeline name
                # That helps shorten pipeline names when using owncloud-ci images
                # that have longer names like 7.4-ubuntu20.04
                phpVersionForPipelineName = phpVersionForDocker[0:3]
                if isWebUI or isAPI or isCLI:
                    esString = "-es" + testConfig["esVersion"] if testConfig["esVersion"] != "none" else ""
                    browserString = "" if testConfig["browser"] == "" else "-" + testConfig["browser"]
                    keyString = "-" + category if testConfig["includeKeyInMatrixName"] else ""
                    partString = "" if testConfig["numberOfParts"] == 1 else "-%d-%d" % (testConfig["numberOfParts"], testConfig["runPart"])
                    name = "%s%s%s-%s%s-%s-php%s%s" % (alternateSuiteName, keyString, partString, testConfig["server"].replace("daily-", "").replace("-qa", ""), browserString, testConfig["database"].replace(":", ""), phpVersionForPipelineName, esString)
                    maxLength = 50
                    nameLength = len(name)
                    if nameLength > maxLength:
                        print("Error: generated stage name of length", nameLength, "is not supported. The maximum length is " + str(maxLength) + ".", name)
                        errorFound = True

                environment = {}
                for env in testConfig["extraEnvironment"]:
                    environment[env] = testConfig["extraEnvironment"][env]

                environment["TEST_SERVER_URL"] = "http://server"
                environment["BEHAT_FILTER_TAGS"] = testConfig["filterTags"]
                environment["DOWNLOADS_DIRECTORY"] = "%s/downloads" % dir["server"]

                if (testConfig["runAllSuites"] == False):
                    environment["BEHAT_SUITE"] = suite
                else:
                    environment["DIVIDE_INTO_NUM_PARTS"] = testConfig["numberOfParts"]
                    environment["RUN_PART"] = testConfig["runPart"]

                if isWebUI:
                    environment["SELENIUM_HOST"] = "selenium"
                    environment["SELENIUM_PORT"] = "4444"
                    environment["BROWSER"] = testConfig["browser"]
                    environment["PLATFORM"] = "Linux"
                    if (testConfig["runCoreTests"]):
                        makeParameter = "test-acceptance-core-webui"
                    else:
                        makeParameter = "test-acceptance-webui"

                if isAPI:
                    if (testConfig["runCoreTests"]):
                        makeParameter = "test-acceptance-core-api"
                    else:
                        makeParameter = "test-acceptance-api"

                if isCLI:
                    if (testConfig["runCoreTests"]):
                        makeParameter = "test-acceptance-core-cli"
                    else:
                        makeParameter = "test-acceptance-cli"

                if testConfig["emailNeeded"]:
                    environment["MAILHOG_HOST"] = "email"

                if testConfig["ldapNeeded"]:
                    environment["TEST_WITH_LDAP"] = True

                if (cephS3Needed or scalityS3Needed):
                    environment["OC_TEST_ON_OBJECTSTORE"] = "1"
                    if (testConfig["cephS3"] != False):
                        environment["S3_TYPE"] = "ceph"
                    if (testConfig["scalityS3"] != False):
                        environment["S3_TYPE"] = "scality"
                federationDbSuffix = "-federated"

                result = {
                    "kind": "pipeline",
                    "type": "docker",
                    "name": name,
                    "workspace": {
                        "base": dir["base"],
                        "path": "testrunner/apps/%s" % ctx.repo.name,
                    },
                    "steps": skipIfUnchanged(ctx, "acceptance-tests") +
                             installCore(ctx, testConfig["server"], testConfig["database"], testConfig["useBundledApp"]) +
                             installTestrunner(ctx, DEFAULT_PHP_VERSION, testConfig["useBundledApp"]) +
                             (installFederated(testConfig["server"], phpVersionForDocker, testConfig["logLevel"], testConfig["database"], federationDbSuffix) + owncloudLog("federated") if testConfig["federatedServerNeeded"] else []) +
                             installAppPhp(ctx, phpVersionForDocker) +
                             installAppJavaScript(ctx) +
                             installExtraApps(phpVersionForDocker, testConfig["extraApps"]) +
                             setupServerAndApp(ctx, phpVersionForDocker, testConfig["logLevel"], testConfig["federatedServerNeeded"], params["enableApp"]) +
                             owncloudLog("server") +
                             setupCeph(testConfig["cephS3"]) +
                             setupScality(testConfig["scalityS3"]) +
                             setupElasticSearch(testConfig["esVersion"]) +
                             testConfig["extraSetup"] +
                             waitForServer(testConfig["federatedServerNeeded"]) +
                             waitForEmailService(testConfig["emailNeeded"]) +
                             fixPermissions(phpVersionForDocker, testConfig["federatedServerNeeded"], params["selUserNeeded"]) +
                             waitForBrowserService(testConfig["browser"]) +
                             [
                                 ({
                                     "name": "acceptance-tests",
                                     "image": OC_CI_PHP % DEFAULT_PHP_VERSION,
                                     "environment": environment,
                                     "commands": testConfig["extraCommandsBeforeTestRun"] + [
                                         "touch %s/saved-settings.sh" % dir["base"],
                                         ". %s/saved-settings.sh" % dir["base"],
                                         "make %s" % makeParameter,
                                     ],
                                     "volumes": [{
                                         "name": "downloads",
                                         "path": "%s/downloads" % dir["server"],
                                     }],
                                 }),
                             ] + testConfig["extraTeardown"] + githubComment(params["earlyFail"]) + stopBuild(ctx, params["earlyFail"]),
                    "services": databaseService(testConfig["database"]) +
                                browserService(testConfig["browser"]) +
                                emailService(testConfig["emailNeeded"]) +
                                ldapService(testConfig["ldapNeeded"]) +
                                cephService(testConfig["cephS3"]) +
                                scalityService(testConfig["scalityS3"]) +
                                elasticSearchService(testConfig["esVersion"]) +
                                testConfig["extraServices"] +
                                owncloudService(testConfig["server"], phpVersionForDocker, "server", dir["server"], testConfig["ssl"], testConfig["xForwardedFor"]) +
                                ((
                                    owncloudService(testConfig["server"], phpVersionForDocker, "federated", dir["federated"], testConfig["ssl"], testConfig["xForwardedFor"]) +
                                    databaseServiceForFederation(testConfig["database"], federationDbSuffix)
                                ) if testConfig["federatedServerNeeded"] else []),
                    "depends_on": [],
                    "trigger": {},
                    "volumes": [{
                        "name": "downloads",
                        "temp": {},
                    }],
                }

                if (testConfig["cron"] != ""):
                    result["trigger"]["cron"] = testConfig["cron"]
                elif ((testConfig["pullRequestAndCron"] != "") and (ctx.build.event != "pull_request")):
                    result["trigger"]["cron"] = testConfig["pullRequestAndCron"]
                else:
                    result["trigger"]["ref"] = [
                        "refs/pull/**",
                        "refs/tags/**",
                    ]

                pipelines.append(result)

    if errorFound:
        return False

    return pipelines

def sonarAnalysis(ctx, phpVersion = DEFAULT_PHP_VERSION):
    sonar_env = {
        "SONAR_TOKEN": {
            "from_secret": "sonar_token",
        },
        "SONAR_SCANNER_OPTS": "-Xdebug",
    }

    if ctx.build.event == "pull_request":
        sonar_env.update({
            "SONAR_PULL_REQUEST_BASE": "%s" % (ctx.build.target),
            "SONAR_PULL_REQUEST_BRANCH": "%s" % (ctx.build.source),
            "SONAR_PULL_REQUEST_KEY": "%s" % (ctx.build.ref.replace("refs/pull/", "").split("/")[0]),
        })

    repo_slug = ctx.build.source_repo if ctx.build.source_repo else ctx.repo.slug

    result = {
        "kind": "pipeline",
        "type": "docker",
        "name": "sonar-analysis",
        "workspace": {
            "base": dir["base"],
            "path": "server/apps/%s" % ctx.repo.name,
        },
        "clone": {
            "disable": True,  # Sonarcloud does not apply issues on already merged branch
        },
        "steps": [
                     {
                         "name": "clone",
                         "image": OC_CI_ALPINE,
                         "commands": [
                             "git clone https://github.com/%s.git ." % repo_slug,
                             "git checkout $DRONE_COMMIT",
                         ],
                     },
                 ] +
                 skipIfUnchanged(ctx, "unit-tests") +
                 cacheRestore() +
                 composerInstall(phpVersion) +
                 installCore(ctx, "daily-master-qa", "sqlite", False) +
                 [
                     {
                         "name": "sync-from-cache",
                         "image": MINIO_MC,
                         "environment": {
                             "MC_HOST_cache": {
                                 "from_secret": "cache_s3_connection_url",
                             },
                         },
                         "commands": [
                             "mkdir -p results",
                             "mc mirror cache/cache/%s/%s results/" % (ctx.repo.slug, ctx.build.commit + "-${DRONE_BUILD_NUMBER}"),
                         ],
                     },
                     {
                         "name": "list-coverage-results",
                         "image": OC_CI_PHP % phpVersion,
                         "commands": [
                             "ls -l results",
                         ],
                     },
                     {
                         "name": "sonarcloud",
                         "image": SONARSOURCE_SONAR_SCANNER_CLI,
                         "environment": sonar_env,
                         "when": {
                             "instance": [
                                 "drone.owncloud.services",
                                 "drone.owncloud.com",
                             ],
                         },
                     },
                     {
                         "name": "purge-cache",
                         "image": MINIO_MC,
                         "environment": {
                             "MC_HOST_cache": {
                                 "from_secret": "cache_s3_connection_url",
                             },
                         },
                         "commands": [
                             "mc rm --recursive --force cache/cache/%s/%s" % (ctx.repo.slug, ctx.build.commit + "-${DRONE_BUILD_NUMBER}"),
                         ],
                     },
                 ],
        "depends_on": [],
        "trigger": {
            "ref": [
                "refs/heads/master",
                "refs/pull/**",
                "refs/tags/**",
            ],
        },
    }

    for branch in config["branches"]:
        result["trigger"]["ref"].append("refs/heads/%s" % branch)

    return result

def notify():
    result = {
        "kind": "pipeline",
        "type": "docker",
        "name": "chat-notifications",
        "clone": {
            "disable": True,
        },
        "steps": [
            {
                "name": "notify-rocketchat",
                "image": PLUGINS_SLACK,
                "settings": {
                    "webhook": {
                        "from_secret": config["rocketchat"]["from_secret"],
                    },
                    "channel": config["rocketchat"]["channel"],
                },
            },
        ],
        "depends_on": [],
        "trigger": {
            "ref": [
                "refs/tags/**",
            ],
            "status": [
                "success",
                "failure",
            ],
        },
    }

    for branch in config["branches"]:
        result["trigger"]["ref"].append("refs/heads/%s" % branch)

    return result

def databaseService(db):
    dbName = getDbName(db)
    if (dbName == "mariadb") or (dbName == "mysql"):
        service = {
            "name": dbName,
            "image": db,
            "environment": {
                "MYSQL_USER": getDbUsername(db),
                "MYSQL_PASSWORD": getDbPassword(db),
                "MYSQL_DATABASE": getDbDatabase(db),
                "MYSQL_ROOT_PASSWORD": getDbRootPassword(),
            },
        }
        if (db == "mysql:8.0"):
            service["command"] = ["--default-authentication-plugin=mysql_native_password"]
        return [service]

    if dbName == "postgres":
        return [{
            "name": dbName,
            "image": db,
            "environment": {
                "POSTGRES_USER": getDbUsername(db),
                "POSTGRES_PASSWORD": getDbPassword(db),
                "POSTGRES_DB": getDbDatabase(db),
            },
        }]

    if dbName == "oracle":
        return [{
            "name": dbName,
            "image": OC_CI_ORACLE_XE,
            "environment": {
                "ORACLE_USER": getDbUsername(db),
                "ORACLE_PASSWORD": getDbPassword(db),
                "ORACLE_DB": getDbDatabase(db),
                "ORACLE_DISABLE_ASYNCH_IO": "true",
            },
        }]

    return []

def browserService(browser):
    if browser == "chrome":
        return [{
            "name": "selenium",
            "image": SELENIUM_STANDALONE_CHROME_DEBUG,
            "environment": {
                "JAVA_OPTS": "-Dselenium.LOGGER.level=WARNING",
            },
            "volumes": [{
                "name": "downloads",
                "path": dir["browserService"],
            }],
        }]

    if browser == "firefox":
        return [{
            "name": "selenium",
            "image": SELENIUM_STANDALONE_FIREFOX_DEBUG,
            "environment": {
                "JAVA_OPTS": "-Dselenium.LOGGER.level=WARNING",
                "SE_OPTS": "-enablePassThrough false",
            },
            "volumes": [{
                "name": "downloads",
                "path": dir["browserService"],
            }],
        }]

    return []

def waitForBrowserService(browser):
    if browser in ["chrome", "firefox"]:
        return [{
            "name": "wait-for-selenium",
            "image": OC_CI_WAIT_FOR,
            "commands": [
                "wait-for -it selenium:4444 -t 600",
            ],
        }]

    return []

def emailService(emailNeeded):
    if emailNeeded:
        return [{
            "name": "email",
            "image": MAILHOG_MAILHOG,
        }]

    return []

def waitForEmailService(emailNeeded):
    if emailNeeded:
        return [{
            "name": "wait-for-email",
            "image": OC_CI_WAIT_FOR,
            "commands": [
                "wait-for -it email:8025 -t 600",
            ],
        }]

    return []

def ldapService(ldapNeeded):
    if ldapNeeded:
        return [{
            "name": "ldap",
            "image": OSIXIA_OPEN_LDAP,
            "environment": {
                "LDAP_DOMAIN": "owncloud.com",
                "LDAP_ORGANISATION": "owncloud",
                "LDAP_ADMIN_PASSWORD": "admin",
                "LDAP_TLS_VERIFY_CLIENT": "never",
            },
        }]

    return []

def elasticSearchService(esVersion):
    if esVersion == "none":
        return []

    return [{
        "name": "elasticsearch",
        "image": OC_OPS_ELASTIC_SEARCH % esVersion,
        "environment": {
            "ELASTICSEARCH_ROOT_LOG_LEVEL": "warn",
            "ELASTICSEARCH_BOOTSTRAP_MEMORY_LOCK": "false",
        },
    }]

def scalityService(serviceParams):
    serviceEnvironment = {
        "HOST_NAME": "scality",
    }

    if type(serviceParams) == "bool":
        if not serviceParams:
            return []
    elif "extraEnvironment" in serviceParams:
        for env in serviceParams["extraEnvironment"]:
            serviceEnvironment[env] = serviceParams["extraEnvironment"][env]

    return [{
        "name": "scality",
        "image": OC_CI_SCALITY_S3_SERVER,
        "environment": serviceEnvironment,
    }]

def cephService(serviceParams):
    serviceEnvironment = {
        "NETWORK_AUTO_DETECT": "4",
        "RGW_NAME": "ceph",
        "CEPH_DEMO_UID": "owncloud",
        "CEPH_DEMO_ACCESS_KEY": "owncloud123456",
        "CEPH_DEMO_SECRET_KEY": "secret123456",
    }

    if type(serviceParams) == "bool":
        if not serviceParams:
            return []
    elif "extraEnvironment" in serviceParams:
        for env in serviceParams["extraEnvironment"]:
            serviceEnvironment[env] = serviceParams["extraEnvironment"][env]

    return [{
        "name": "ceph",
        "image": OC_CI_CEPH,
        "environment": serviceEnvironment,
    }]

def owncloudService(version, phpVersion, name, path, ssl, xForwardedFor):
    if ssl:
        environment = {
            "APACHE_WEBROOT": path,
            "APACHE_CONFIG_TEMPLATE": "ssl",
            "APACHE_SSL_CERT_CN": "server",
            "APACHE_SSL_CERT": "%s/%s.crt" % (dir["base"], name),
            "APACHE_SSL_KEY": "%s/%s.key" % (dir["base"], name),
            "APACHE_LOGGING_PATH": "/dev/null",
        }
    else:
        environment = {
            "APACHE_WEBROOT": path,
            "APACHE_LOGGING_PATH": "/dev/null",
        }

    return [{
        "name": name,
        "image": OC_CI_PHP % phpVersion,
        "environment": environment,
        "commands": ([
            "a2enmod remoteip",
            "cd /etc/apache2",
            "echo 'RemoteIPHeader X-Forwarded-For' >> apache2.conf",
            # This replaces the first occurrence of "%h with "%a in apache2.conf file telling Apache to log the client
            # IP as recorded by mod_remoteip (%a) rather than hostname (%h). For more info check this out:
            # https://www.digitalocean.com/community/questions/get-client-public-ip-on-apache-server-used-behind-load-balancer
            "sed -i '0,/\"%h/s//\"%a/' apache2.conf",
        ] if xForwardedFor else []) + [
            "/usr/local/bin/apachectl -e debug -D FOREGROUND",
        ],
    }]

def getDbName(db):
    return db.split(":")[0]

def getDbUsername(db):
    name = getDbName(db)

    # The Oracle image has the Db Username hardcoded
    if name == "oracle":
        return "autotest"

    return "owncloud"

def getDbPassword(db):
    name = getDbName(db)

    # The Oracle image has the Db Password hardcoded
    if name == "oracle":
        return "owncloud"

    return "owncloud"

def getDbRootPassword():
    return "owncloud"

def getDbDatabase(db):
    name = getDbName(db)

    # The Oracle image has the Db Name hardcoded
    if name == "oracle":
        return "XE"

    return "owncloud"

def getNodeJsVersion():
    if "nodeJsVersion" not in config:
        # We use nodejs 14 as the default
        return DEFAULT_NODEJS_VERSION
    else:
        return config["nodeJsVersion"]

def cacheRestore():
    return [{
        "name": "cache-restore",
        "image": PLUGINS_S3_CACHE,
        "settings": {
            "access_key": {
                "from_secret": "cache_s3_access_key",
            },
            "endpoint": {
                "from_secret": "cache_s3_endpoint",
            },
            "restore": True,
            "secret_key": {
                "from_secret": "cache_s3_secret_key",
            },
        },
        "when": {
            "instance": [
                "drone.owncloud.services",
                "drone.owncloud.com",
            ],
        },
    }]

def composerInstall(phpVersion):
    return [{
        "name": "composer-install",
        "image": OC_CI_PHP % phpVersion,
        "environment": {
            "COMPOSER_HOME": "/drone/src/.cache/composer",
        },
        "commands": [
            "make vendor",
        ],
    }]

def installCore(ctx, version, db, useBundledApp):
    host = getDbName(db)
    dbType = host

    username = getDbUsername(db)
    password = getDbPassword(db)
    database = getDbDatabase(db)

    if host == "mariadb":
        dbType = "mysql"

    if host == "postgres":
        dbType = "pgsql"

    if host == "oracle":
        dbType = "oci"

    stepDefinition = {
        "name": "install-core",
        "image": OC_CI_CORE,
        "settings": {
            "version": version,
            "core_path": dir["server"],
            "db_type": dbType,
            "db_name": database,
            "db_host": host,
            "db_username": username,
            "db_password": password,
        },
    }

    if not useBundledApp:
        stepDefinition["settings"]["exclude"] = "apps/%s" % ctx.repo.name

    return [stepDefinition]

def installTestrunner(ctx, phpVersion, useBundledApp):
    return [{
        "name": "install-testrunner",
        "image": OC_CI_PHP % phpVersion,
        "commands": [
            "mkdir /tmp/testrunner",
            "git clone -b master --depth=1 https://github.com/owncloud/core.git /tmp/testrunner",
            "rsync -aIX /tmp/testrunner %s" % dir["base"],
        ] + ([
            "cp -r %s/apps/%s %s/apps/" % (dir["testrunner"], ctx.repo.name, dir["server"]),
        ] if not useBundledApp else []),
    }]

def installExtraApps(phpVersion, extraApps):
    commandArray = []
    for app, command in extraApps.items():
        commandArray.append("ls %s/apps/%s || git clone https://github.com/owncloud/%s.git %s/apps/%s" % (dir["testrunner"], app, app, dir["testrunner"], app))
        commandArray.append("ls %s/apps/%s || cp -r %s/apps/%s %s/apps/" % (dir["server"], app, dir["testrunner"], app, dir["server"]))
        if (command != ""):
            commandArray.append("cd %s/apps/%s" % (dir["server"], app))
            commandArray.append(command)
        commandArray.append("cd %s" % dir["server"])
        commandArray.append("php occ a:l")
        commandArray.append("php occ a:e %s" % app)
        commandArray.append("php occ a:l")

    if (commandArray == []):
        return []

    return [{
        "name": "install-extra-apps",
        "image": OC_CI_PHP % phpVersion,
        "commands": commandArray,
    }]

def installAppPhp(ctx, phpVersion):
    if "appInstallCommandPhp" not in config:
        return []

    # config["appInstallCommandPhp"] must be the command that is needed to
    # install just the PHP-related part of the app. The docker image has PHP
    # and "base" tools. But it does not have JavaScript tools like nodejs,
    # npm, yarn etc.
    return [
        {
            "name": "install-app-php-%s" % ctx.repo.name,
            "image": OC_CI_PHP % phpVersion,
            "commands": [
                "cd %s/apps/%s" % (dir["server"], ctx.repo.name),
                config["appInstallCommandPhp"],
            ],
        },
    ]

def installAppJavaScript(ctx):
    nothingToDo = True
    commandArray = [
        "cd %s/apps/%s" % (dir["server"], ctx.repo.name),
    ]

    if "appInstallCommandJavaScript" in config:
        nothingToDo = False
        commandArray.append(config["appInstallCommandJavaScript"])

    if "buildJsDeps" in config:
        if config["buildJsDeps"]:
            nothingToDo = False
            commandArray.append("make install-js-deps")
            commandArray.append("make build-dev")

    if (nothingToDo):
        return []

    return [
        {
            "name": "install-app-js-%s" % ctx.repo.name,
            "image": OC_CI_NODEJS % getNodeJsVersion(),
            "commands": commandArray,
        },
    ]

def setupServerAndApp(ctx, phpVersion, logLevel, federatedServerNeeded = False, enableApp = True):
    return [{
        "name": "setup-server-%s" % ctx.repo.name,
        "image": OC_CI_PHP % phpVersion,
        "commands": [
            "cd %s" % dir["server"],
            "php occ a:l",
            "php occ a:e %s" % ctx.repo.name if enableApp else "",
            "php occ a:e testing",
            "php occ a:l",
            "php occ config:system:set trusted_domains 1 --value=server",
            "php occ log:manage --level %s" % logLevel,
            "php occ config:system:set csrf.disabled --value=true" if federatedServerNeeded else "",
        ],
    }]

def setupCeph(serviceParams):
    if type(serviceParams) == "bool":
        if serviceParams:
            # specify an empty dict that will get the defaults
            serviceParams = {}
        else:
            return []

    createFirstBucket = serviceParams["createFirstBucket"] if "createFirstBucket" in serviceParams else True
    setupCommands = serviceParams["setupCommands"] if "setupCommands" in serviceParams else [
        "cd %s/apps/files_primary_s3" % dir["server"],
        "cp tests/drone/ceph.config.php %s/config" % dir["server"],
        "cd %s" % dir["server"],
    ]

    return [
        {
            "name": "wait-for-ceph",
            "image": OC_CI_WAIT_FOR,
            "commands": [
                "wait-for -it ceph:80 -t 600",
            ],
        },
        {
            "name": "setup-ceph",
            "image": OC_CI_PHP % DEFAULT_PHP_VERSION,
            "commands": setupCommands + ([
                "./apps/files_primary_s3/tests/drone/create-bucket.sh",
            ] if createFirstBucket else []),
        },
    ]

def setupScality(serviceParams):
    if type(serviceParams) == "bool":
        if serviceParams:
            # specify an empty dict that will get the defaults
            serviceParams = {}
        else:
            return []

    specialConfig = "." + serviceParams["config"] if "config" in serviceParams else ""
    configFile = "scality%s.config.php" % specialConfig
    createFirstBucket = serviceParams["createFirstBucket"] if "createFirstBucket" in serviceParams else True
    createExtraBuckets = serviceParams["createExtraBuckets"] if "createExtraBuckets" in serviceParams else False
    setupCommands = serviceParams["setupCommands"] if "setupCommands" in serviceParams else [
        "cd %s/apps/files_primary_s3" % dir["server"],
        "cp tests/drone/%s %s/config" % (configFile, dir["server"]),
        "cd %s" % dir["server"],
    ]

    return [
        {
            "name": "wait-for-scality",
            "image": OC_CI_WAIT_FOR,
            "commands": [
                "wait-for -it scality:8000 -t 600",
            ],
        },
        {
            "name": "setup-scality",
            "image": OC_CI_PHP % DEFAULT_PHP_VERSION,
            "commands": setupCommands + ([
                "php occ s3:create-bucket owncloud --accept-warning",
            ] if createFirstBucket else []) + ([
                "for I in $(seq 1 9); do php ./occ s3:create-bucket owncloud$I --accept-warning; done",
            ] if createExtraBuckets else []),
        },
    ]

def setupElasticSearch(esVersion):
    if esVersion == "none":
        return []

    return [
        {
            "name": "wait-for-es",
            "image": OC_CI_WAIT_FOR,
            "commands": [
                "wait-for -it elasticsearch:9200 -t 600",
            ],
        },
        {
            "name": "setup-es",
            "image": OC_CI_PHP % DEFAULT_PHP_VERSION,
            "commands": [
                "cd %s" % dir["server"],
                "php occ config:app:set search_elastic servers --value elasticsearch",
                "php occ search:index:reset --force",
            ],
        },
    ]

def waitForServer(federatedServerNeeded):
    return [{
        "name": "wait-for-server",
        "image": OC_CI_WAIT_FOR,
        "commands": [
            "wait-for -it server:80 -t 600",
        ] + ([
            "wait-for -it federated:80 -t 600",
        ] if federatedServerNeeded else []),
    }]

def fixPermissions(phpVersion, federatedServerNeeded, selUserNeeded = False):
    return [{
        "name": "fix-permissions",
        "image": OC_CI_PHP % phpVersion,
        "commands": [
            "chown -R www-data %s" % dir["server"],
        ] + ([
            "chown -R www-data %s" % dir["federated"],
        ] if federatedServerNeeded else []) + ([
            "chmod 777 %s" % dir["browserService"],
        ] if selUserNeeded else []),
        "volumes": [{
            "name": "downloads",
            "path": dir["browserService"],
        }],
    }]

def owncloudLog(server):
    return [{
        "name": "owncloud-log-%s" % server,
        "image": OC_UBUNTU,
        "detach": True,
        "commands": [
            "tail -f %s/%s/data/owncloud.log" % (dir["base"], server),
        ],
    }]

def dependsOn(earlierStages, nextStages):
    for earlierStage in earlierStages:
        for nextStage in nextStages:
            nextStage["depends_on"].append(earlierStage["name"])

def installFederated(federatedServerVersion, phpVersion, logLevel, db, dbSuffix = "-federated"):
    host = getDbName(db)
    dbType = host

    username = getDbUsername(db)
    password = getDbPassword(db)
    database = getDbDatabase(db) + dbSuffix

    if host == "mariadb":
        dbType = "mysql"
    elif host == "postgres":
        dbType = "pgsql"
    elif host == "oracle":
        dbType = "oci"
    return [
        {
            "name": "install-federated",
            "image": OC_CI_CORE,
            "settings": {
                "version": federatedServerVersion,
                "core_path": dir["federated"],
                "db_type": "mysql",
                "db_name": database,
                "db_host": host + dbSuffix,
                "db_username": username,
                "db_password": password,
            },
        },
        {
            "name": "configure-federation",
            "image": OC_CI_PHP % phpVersion,
            "commands": [
                "echo 'export TEST_SERVER_FED_URL=http://federated' > %s/saved-settings.sh" % dir["base"],
                "cd %s" % dir["federated"],
                "php occ a:l",
                "php occ a:e files_external",
                "php occ a:e testing",
                "php occ a:l",
                "php occ config:system:set trusted_domains 1 --value=federated",
                "php occ log:manage --level %s" % logLevel,
                "php occ config:list",
            ],
        },
    ]

def databaseServiceForFederation(db, suffix):
    dbName = getDbName(db)

    if dbName not in ["mariadb", "mysql"]:
        print("Not implemented federated database for ", dbName)
        return []

    service = {
        "name": dbName + suffix,
        "image": db,
        "environment": {
            "MYSQL_USER": getDbUsername(db),
            "MYSQL_PASSWORD": getDbPassword(db),
            "MYSQL_DATABASE": getDbDatabase(db) + suffix,
            "MYSQL_ROOT_PASSWORD": getDbRootPassword(),
        },
    }
    if (db == "mysql:8.0"):
        service["command"] = ["--default-authentication-plugin=mysql_native_password"]
    return [service]

def buildTestConfig(params):
    configs = []
    for server in params["servers"]:
        for browser in params["browsers"]:
            for phpVersion in params["phpVersions"]:
                for db in params["databases"]:
                    for esVersion in params["esVersions"]:
                        for runPart in range(1, params["numberOfParts"] + 1):
                            config = dict(params)
                            config["server"] = server
                            config["browser"] = browser
                            config["phpVersion"] = phpVersion
                            config["database"] = db
                            config["esVersion"] = esVersion
                            config["runPart"] = runPart
                            configs.append(config)
    return configs

def stopBuild(ctx, earlyFail):
    if (earlyFail):
        return [{
            "name": "stop-build",
            "image": DRONE_CLI,
            "environment": {
                "DRONE_SERVER": "https://drone.owncloud.com",
                "DRONE_TOKEN": {
                    "from_secret": "drone_token",
                },
            },
            "commands": [
                "drone build stop owncloud/%s ${DRONE_BUILD_NUMBER}" % ctx.repo.name,
            ],
            "when": {
                "status": [
                    "failure",
                ],
                "event": [
                    "pull_request",
                ],
            },
        }]

    else:
        return []

def githubComment(earlyFail):
    if (earlyFail):
        return [{
            "name": "github-comment",
            "image": THEGEEKLAB_DRONE_GITHUB_COMMENT,
            "pull": "if-not-exists",
            "settings": {
                "message": ":boom: Acceptance tests pipeline <strong>${DRONE_STAGE_NAME}</strong> failed. The build has been cancelled.\\n\\n${DRONE_BUILD_LINK}/${DRONE_JOB_NUMBER}${DRONE_STAGE_NUMBER}",
                "key": "pr-${DRONE_PULL_REQUEST}",
                "update": "true",
                "api_key": {
                    "from_secret": "github_token",
                },
            },
            "when": {
                "status": [
                    "failure",
                ],
                "event": [
                    "pull_request",
                ],
            },
        }]

    else:
        return []

def checkStarlark():
    return [{
        "kind": "pipeline",
        "type": "docker",
        "name": "check-starlark",
        "steps": [
            {
                "name": "format-check-starlark",
                "image": OC_CI_BAZEL_BUILDIFIER,
                "commands": [
                    "buildifier --mode=check .drone.star",
                ],
            },
            {
                "name": "show-diff",
                "image": OC_CI_BAZEL_BUILDIFIER,
                "commands": [
                    "buildifier --mode=fix .drone.star",
                    "git diff",
                ],
                "when": {
                    "status": [
                        "failure",
                    ],
                },
            },
        ],
        "depends_on": [],
        "trigger": {
            "ref": [
                "refs/pull/**",
            ],
        },
    }]

def phplint(ctx):
    pipelines = []

    if "phplint" not in config:
        return pipelines

    if type(config["phplint"]) == "bool":
        if not config["phplint"]:
            return pipelines

    result = {
        "kind": "pipeline",
        "type": "docker",
        "name": "lint-test",
        "workspace": {
            "base": dir["base"],
            "path": "server/apps/%s" % ctx.repo.name,
        },
        "steps": skipIfUnchanged(ctx, "lint") +
                 installNPM() +
                 lintTest(),
        "depends_on": [],
        "trigger": {
            "ref": [
                "refs/heads/master",
                "refs/tags/**",
                "refs/pull/**",
            ],
        },
    }

    for branch in config["branches"]:
        result["trigger"]["ref"].append("refs/heads/%s" % branch)

    pipelines.append(result)

    return pipelines

def installNPM():
    return [{
        "name": "npm-install",
        "image": OC_CI_NODEJS % getNodeJsVersion(),
        "commands": [
            "yarn install --frozen-lockfile",
        ],
    }]

def lintTest():
    return [{
        "name": "lint-test",
        "image": OC_CI_PHP % DEFAULT_PHP_VERSION,
        "commands": [
            "make test-lint",
        ],
    }]

def skipIfUnchanged(ctx, type):
    if ("full-ci" in ctx.build.title.lower()):
        return []

    skip_step = {
        "name": "skip-if-unchanged",
        "image": OC_CI_DRONE_SKIP_PIPELINE,
        "when": {
            "event": [
                "pull_request",
            ],
        },
    }

    # these files are not relevant for test pipelines
    # if only files in this array are changed, then don't even run the "lint"
    # pipelines (like code-style, phan, phpstan...)
    allow_skip_if_changed = [
        "^.github/.*",
        "^changelog/.*",
        "^docs/.*",
        "CHANGELOG.md",
        "CONTRIBUTING.md",
        "LICENSE",
        "LICENSE.md",
        "README.md",
    ]

    if type == "lint":
        skip_step["settings"] = {
            "ALLOW_SKIP_CHANGED": allow_skip_if_changed,
        }
        return [skip_step]

    if type == "acceptance-tests":
        # if any of these files are touched then run all acceptance tests
        # note: some oC10 apps have various directories like handlers, rules, etc.
        #       so those are all listed here so that this starlark code can be
        #       the same for every oC10 app.
        acceptance_files = [
            "^tests/acceptance/.*",
            "^tests/drone/.*",
            "^tests/TestHelpers/.*",
            "^vendor-bin/behat/.*",
            "^appinfo/.*",
            "^command/.*",
            "^controller/.*",
            "^css/.*",
            "^db/.*",
            "^handlers/.*",
            "^js/.*",
            "^lib/.*",
            "^rules/.*",
            "^src/.*",
            "^templates/.*",
            "composer.json",
            "composer.lock",
            "Makefile",
            "package.json",
            "package-lock.json",
            "yarn.lock",
        ]
        skip_step["settings"] = {
            "DISALLOW_SKIP_CHANGED": acceptance_files,
        }
        return [skip_step]

    if type == "unit-tests":
        # if any of these files are touched then run all unit tests
        # note: some oC10 apps have various directories like handlers, rules, etc.
        #       so those are all listed here so that this starlark code can be
        #       the same for every oC10 app.
        unit_files = [
            "^tests/integration/.*",
            "^tests/js/.*",
            "^tests/Unit/.*",
            "^tests/unit/.*",
            "^appinfo/.*",
            "^command/.*",
            "^controller/.*",
            "^css/.*",
            "^db/.*",
            "^handlers/.*",
            "^js/.*",
            "^lib/.*",
            "^rules/.*",
            "^src/.*",
            "^templates/.*",
            "composer.json",
            "composer.lock",
            "Makefile",
            "package.json",
            "package-lock.json",
            "phpunit.xml",
            "yarn.lock",
            "sonar-project.properties",
        ]
        skip_step["settings"] = {
            "DISALLOW_SKIP_CHANGED": unit_files,
        }
        return [skip_step]

    return []
