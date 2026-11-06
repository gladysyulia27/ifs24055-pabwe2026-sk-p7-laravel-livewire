pipeline {
    agent any

    options {
        timestamps()
        disableConcurrentBuilds()
    }

    environment {
        APP_ENV = 'testing'
        PHP_CONTAINER_IMAGE = 'delcom-ci-php:8.5'
        NODE_CONTAINER_IMAGE = 'node:24-alpine'
        TRIVY_CONTAINER_IMAGE = 'aquasec/trivy:0.74.0'
        TRIVY_CACHE_DIR = "${WORKSPACE}/.trivy-cache"
    }

    stages {
        stage('Checkout Info') {
            steps {
                echo "Menjalankan build dari branch: ${env.BRANCH_NAME ?: 'local'}"
            }
        }

        stage('Dependency Installation') {
            parallel {
                stage('Install PHP Dependencies') {
                    steps {
                        script {
                            runInContainer(env.PHP_CONTAINER_IMAGE, '''
                                set -eu
                                php -v
                                composer --version
                                composer validate --no-check-publish
                                composer install --no-interaction --prefer-dist --no-progress
                            ''')
                        }
                    }
                }

                stage('Install Frontend Dependencies') {
                    steps {
                        script {
                            runInContainer(env.NODE_CONTAINER_IMAGE, '''
                                set -eu
                                node -v
                                npm -v
                                if [ -f package-lock.json ]; then
                                    npm ci --ignore-scripts --no-audit --no-fund
                                else
                                    npm install --ignore-scripts --no-audit --no-fund
                                fi
                            ''')
                        }
                    }
                }
            }
        }

        stage('Quality & Security Gate') {
            parallel {
                stage('Tests & 100% Coverage') {
                    steps {
                        script {
                            runInContainer(env.PHP_CONTAINER_IMAGE, '''
                                set -eu
                                cp -f .env.example .env
                                php artisan key:generate --force
                                php -d pcov.enabled=1 -d pcov.directory=app vendor/bin/phpunit --configuration phpunit.xml --coverage-clover coverage.xml --coverage-text --log-junit junit.xml
                            ''')

                            assertFullCoverage('coverage.xml')
                        }
                    }
                }

                stage('Build Frontend Assets') {
                    steps {
                        script {
                            runInContainer(env.NODE_CONTAINER_IMAGE, '''
                                set -eu
                                npm run build
                            ''')
                        }
                    }
                }

                stage('Security Advisories & Vulnerability Scan') {
                    steps {
                        script {
                            runInContainer(env.PHP_CONTAINER_IMAGE, '''
                                set -eu
                                composer audit --locked --no-interaction
                            ''')

                            runInContainer(env.NODE_CONTAINER_IMAGE, '''
                                set -eu
                                if [ -f package-lock.json ]; then
                                    npm audit --audit-level=high
                                fi
                            ''')

                            runInContainer(env.TRIVY_CONTAINER_IMAGE, '''
                                set -eu
                                mkdir -p "$TRIVY_CACHE_DIR"
                                trivy fs \
                                    --cache-dir "$TRIVY_CACHE_DIR" \
                                    --scanners vuln,secret,misconfig \
                                    --severity CRITICAL,HIGH \
                                    --ignore-unfixed \
                                    --Skip-dirs vendor,node_modules,.git,storage,bootstrap/cache,.trivy-cache \
                                    --format sarif \
                                    --output trivy-results.sarif \
                                    --exit-code 1 \
                                    .
                            ''', '--entrypoint ""')
                        }
                    }
                }
            }
        }
    }

    post {
        always {
            junit allowEmptyResults: true, testResults: 'junit.xml'
            archiveArtifacts artifacts: 'coverage.xml,coverage.txt,trivy-results.sarif', allowEmptyArchive: true
            recordIssues enabledForFailure: true, tool: sarif(pattern: 'trivy-results.sarif')
        }
        success {
            echo 'Pipeline berhasil: test, 100% coverage, build asset, dan security scan lulus.'
        }
        failure {
            echo 'Pipeline gagal: periksa hasil test, coverage, atau temuan keamanan.'
        }
    }
}

def runInContainer(String image, String commands, String extraDockerArgs = '') {
    def scriptBody = """
        set -eu
        docker run --rm \\
            -v "${WORKSPACE}:/workspace" \\
            -w /workspace \\
            -e APP_ENV="${env.APP_ENV}" \\
            -e TRIVY_CACHE_DIR="/workspace/.trivy-cache" \\
            ${extraDockerArgs} \\
            ${image} \\
            sh -lc '${commands.replace("'", "'\"'\"'")}'
    """.stripIndent()

    if (isUnix()) {
        sh(scriptBody)
    } else {
        bat("""
            @echo off
            docker run --rm -v "%WORKSPACE%:/workspace" -w /workspace -e APP_ENV=%APP_ENV% -e TRIVY_CACHE_DIR=/workspace/.trivy-cache ${extraDockerArgs} ${image} sh -lc "${commands.replace('"', '\\"').replace('\n', ' && ')}"
        """.stripIndent())
    }
}

def assertFullCoverage(String cloverFilePath) {
    if (!fileExists(cloverFilePath)) {
        error("File coverage '${cloverFilePath}' tidak ditemukan.")
    }

    def xmlContent = readFile(cloverFilePath)
    def statementsMatch = (xmlContent =~ /<project[^>]*>[\s\S]*?<metrics[^>]*statements="(\d+)"[^>]*coveredstatements="(\d+)"[^>]*methods="(\d+)"[^>]*coveredmethods="(\d+)"[^>]*elements="(\d+)"[^>]*coveredelements="(\d+)"/)

    if (!statementsMatch.find()) {
        error('Gagal membaca metrik coverage dari coverage.xml.')
    }

    int statements = statementsMatch.group(1) as int
    int coveredStatements = statementsMatch.group(2) as int
    int methods = statementsMatch.group(3) as int
    int coveredMethods = statementsMatch.group(4) as int
    int elements = statementsMatch.group(5) as int
    int coveredElements = statementsMatch.group(6) as int

    echo "Coverage Summary -> Statements: ${coveredStatements}/${statements}, Methods: ${coveredMethods}/${methods}, Elements: ${coveredElements}/${elements}"

    if (statements == 0 || coveredStatements != statements || coveredMethods != methods || coveredElements != elements) {
        error("Coverage belum 100%! Statements: ${coveredStatements}/${statements}, Methods: ${coveredMethods}/${methods}, Elements: ${coveredElements}/${elements}")
    }
}
