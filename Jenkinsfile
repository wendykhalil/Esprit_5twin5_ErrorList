// SolarShare CI. No production deploy and no SonarQube server is configured here.
//
// PowerShell, from this repository, with Docker Desktop running:
//   docker compose -f docker-compose.jenkins.yml up -d
//   docker compose -f docker-compose.jenkins.yml stop
//   docker compose -f docker-compose.jenkins.yml down
// Do not add -v. That would delete the Jenkins home volume.
//
// The Jenkins container reaches the Docker engine through the host socket.
// That socket can start, stop, and modify every container on this machine.
// Keep the published ports on 127.0.0.1 and do not expose Jenkins to a network.

pipeline {
    agent any

    options {
        timestamps()
        disableConcurrentBuilds()
        skipDefaultCheckout(true)
        timeout(time: 45, unit: 'MINUTES')
    }

    environment {
        CI_IMAGE = 'solarshare-ci:local'
        APP_IMAGE = 'solarshare:ci'
        DOCKER_BUILDKIT = '1'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('CI image') {
            steps {
                sh '''
                    set -eu
                    start=$(date +%s)
                    docker build -f docker/ci.Dockerfile -t "$CI_IMAGE" .
                    echo "STAGE_SECONDS=$(( $(date +%s) - start ))"
                '''
            }
        }

        stage('PHPUnit') {
            steps {
                sh '''
                    set -eu
                    start=$(date +%s)
                    name="solarshare-ci-${BUILD_NUMBER}"
                    rm -rf build/ci-reports
                    mkdir -p build/ci-reports
                    docker rm -f "$name" >/dev/null 2>&1 || true
                    docker create --name "$name" "$CI_IMAGE"
                    set +e
                    docker start -a "$name"
                    status=$?
                    set -e
                    docker cp "$name":/reports/. build/ci-reports
                    docker rm -f "$name" >/dev/null
                    echo "STAGE_SECONDS=$(( $(date +%s) - start ))"
                    test "$status" = "0"
                '''
            }
        }

        stage('Application image') {
            steps {
                sh '''
                    set -eu
                    start=$(date +%s)
                    docker build -t "$APP_IMAGE" .
                    echo "STAGE_SECONDS=$(( $(date +%s) - start ))"
                '''
            }
        }
    }

    post {
        always {
            junit allowEmptyResults: false, testResults: 'build/ci-reports/junit.xml'
        }
    }
}
