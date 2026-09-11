# C-Net AI Studio Offline Desktop V1

This directory is an isolated desktop application. It does not replace the Laravel production application or Worker V5.2.

Locked architecture: one universal Windows installer, runtime hardware scanning, and a built-in Model Manager supporting Auto, Suggested, and Manual engine selection. Verified packages can be downloaded or imported offline, activated per tool, and safely removed. Engine metadata is data-driven so future hardware and model packages do not require rebuilding the main application.

Model downloads remain disabled until a model publisher URL and SHA-256 checksum are entered in `registry/engines.json`; the application never installs an unverified package. The local runner uses structured standard input and never invokes a command shell.

Run `npm test` for recommendation tests, `npm start` for development, and `npm run dist:win` on Windows to produce the NSIS installer.
