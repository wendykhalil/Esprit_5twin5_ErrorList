# SolarShare continuous delivery

The Jenkins pipeline keeps the existing CI stages, then delivers one immutable image. It does not deploy to production.

## Pipeline

1. Checkout
2. CI image
3. PHPUnit, with the JUnit report published when `build/ci-reports/junit.xml` exists
4. SonarQube Analysis
5. Application image, tagged `solarshare:<git-sha>` and `ghcr.io/wendykhalil/esprit_5twin5_errorlist:<git-sha>`
6. Publish image to GHCR
7. Approve test deploy (manual)
8. Deploy that same GHCR tag to the local test stack and check `http://127.0.0.1:8010/up`

The test stack is `docker-compose.cd.yml`. It has no `build` key. It does not use the development MySQL volume.

## Required Jenkins credential

Create a Username with password credential:

| Field | Value |
|---|---|
| ID | `ghcr-solarshare` |
| Username | GitHub user allowed to push packages |
| Password | GitHub token with `write:packages` and `read:packages` |

Do not store that token in Git. If this credential is missing, or if the login, push, or remote tag check fails, the Publish image stage fails the build. The remote check runs before logout, because a private GHCR package cannot be inspected anonymously. The test deploy does not run, so a green build cannot hide a missing delivery.

## Deliver a version

After the manual approval, Jenkins runs:

```bash
docker pull ghcr.io/wendykhalil/esprit_5twin5_errorlist:<git-sha>
SOLARSHARE_IMAGE=ghcr.io/wendykhalil/esprit_5twin5_errorlist:<git-sha> \
  docker compose -f docker-compose.cd.yml -p solarshare-cd up -d --no-build --pull never
```

Jenkins then calls `/up` inside the new container. On the Windows host the same application is `http://127.0.0.1:8010/up`.

## Roll back

Use the previous commit SHA. Do not rebuild and do not delete volumes.

```bash
docker pull ghcr.io/wendykhalil/esprit_5twin5_errorlist:<previous-sha>
SOLARSHARE_IMAGE=ghcr.io/wendykhalil/esprit_5twin5_errorlist:<previous-sha> \
  docker compose -f docker-compose.cd.yml -p solarshare-cd up -d --no-build --pull never
```

Stop the test stack without deleting its database:

```bash
docker compose -f docker-compose.cd.yml -p solarshare-cd stop
```
