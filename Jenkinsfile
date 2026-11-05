pipeline {
  agent any
  stages {
    stage('Checkout') { steps { checkout scm } }
    stage('Install dependencies') { steps { sh 'composer install --no-interaction --prefer-dist' } }
    stage('Security audit') { steps { sh 'composer audit --no-interaction' } }
    stage('Tests and coverage') { steps { sh 'vendor/bin/phpunit --coverage-clover=coverage.xml' } }
    stage('Coverage gate') { steps { sh 'php scripts/coverage-gate.php coverage.xml' } }
  }
}
