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
                script {
                    env.COMMIT_SHA = sh(script: 'git rev-parse HEAD', returnStdout: true).trim()
                    if (!env.COMMIT_SHA) {
                        error('git rev-parse HEAD did not return a commit SHA')
                    }
                    env.GHCR_IMAGE = "ghcr.io/wendykhalil/esprit_5twin5_errorlist:${env.COMMIT_SHA}"
                }
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
        stage('SonarQube Analysis') {
            steps {
                script {
                    def scannerHome = tool 'SonarScanner'

                    withSonarQubeEnv('SonarQube-SolarShare') {
                        sh "set -eu; '${scannerHome}/bin/sonar-scanner'"
                    }
                }
            }
        }

        stage('Application image') {
            steps {
                sh '''
                    set -eu
                    test -n "$COMMIT_SHA"
                    test -n "$GHCR_IMAGE"
                    start=$(date +%s)
                    docker build -t "$APP_IMAGE" -t "solarshare:${COMMIT_SHA}" -t "$GHCR_IMAGE" .
                    echo "STAGE_SECONDS=$(( $(date +%s) - start ))"
                '''
            }
        }

        stage('Publish image') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'ghcr-solarshare', usernameVariable: 'GHCR_USER', passwordVariable: 'GHCR_TOKEN')]) {
                    sh '''
                        set -eu
                        test -n "$GHCR_IMAGE"
                        echo "$GHCR_TOKEN" | docker login ghcr.io -u "$GHCR_USER" --password-stdin
                        status=0
                        docker push "$GHCR_IMAGE" || status=$?
                        if [ "$status" -eq 0 ]; then
                            docker buildx imagetools inspect "$GHCR_IMAGE" || status=$?
                        fi
                        docker logout ghcr.io
                        exit "$status"
                    '''
                }
                script {
                    env.IMAGE_PUBLISHED = 'true'
                }
            }
        }

        stage('Approve test deploy') {
            when {
                expression { return env.IMAGE_PUBLISHED == 'true' }
            }
            steps {
                input message: "Deploy ghcr.io/wendykhalil/esprit_5twin5_errorlist:${env.COMMIT_SHA} to the local test environment? This is not production.", ok: 'Deploy'
            }
        }

        stage('Deploy test') {
            when {
                expression { return env.IMAGE_PUBLISHED == 'true' }
            }
            steps {
                sh '''
                    set -eu
                    start=$(date +%s)
                    docker pull "$GHCR_IMAGE"
                    SOLARSHARE_IMAGE="$GHCR_IMAGE" docker compose -f docker-compose.cd.yml -p solarshare-cd up -d --no-build --pull never
                    ready=0
                    i=0
                    while [ "$i" -lt 30 ]; do
                        if SOLARSHARE_IMAGE="$GHCR_IMAGE" docker compose -f docker-compose.cd.yml -p solarshare-cd exec -T app php -r "exit(@file_get_contents('http://127.0.0.1:8000/up') === false ? 1 : 0);"; then
                            ready=1
                            break
                        fi
                        i=$((i + 1))
                        sleep 2
                    done
                    echo "STAGE_SECONDS=$(( $(date +%s) - start ))"
                    test "$ready" = "1"
                '''
            }
        }
    }

    post {
        always {
            script {
                if (fileExists('build/ci-reports/junit.xml')) {
                    junit testResults: 'build/ci-reports/junit.xml'
                } else {
                    echo 'No JUnit report: PHPUnit did not run because an earlier stage failed.'
                }
            }
        }
    }
}
