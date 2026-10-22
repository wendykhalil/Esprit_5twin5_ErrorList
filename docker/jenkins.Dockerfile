# Jenkins talks to Docker Desktop through the host socket. See docker-compose.jenkins.yml.
# That socket can control every container on the machine. Do not publish this image.
#
# The Jenkinsfile enables BuildKit. Debian's docker-cli does not ship the Buildx
# plugin, and `docker build` then fails before any image layer is created.
# docker-buildx is the matching Debian 13 plugin. The image base is Trixie,
# so the Docker apt repository must not be pinned to Bookworm.

FROM jenkins/jenkins:lts-jdk21

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        docker-cli \
        docker-buildx \
    && docker buildx version \
    && rm -rf /var/lib/apt/lists/*

USER jenkins
