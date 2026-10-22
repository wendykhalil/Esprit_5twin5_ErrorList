# Adds the Docker CLI so the Jenkinsfile can stream a build context to Docker Desktop.
# The container still needs the host socket, which is a high-risk mount. See docker-compose.jenkins.yml.

FROM jenkins/jenkins:lts-jdk21

USER root

RUN apt-get update && apt-get install -y --no-install-recommends ca-certificates curl \
    && install -m 0755 -d /etc/apt/keyrings \
    && curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc \
    && chmod a+r /etc/apt/keyrings/docker.asc \
    && echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian bookworm stable" > /etc/apt/sources.list.d/docker.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends docker-cli \
    && rm -rf /var/lib/apt/lists/*

USER jenkins
