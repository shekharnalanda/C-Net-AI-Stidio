# C-Net AI Studio Offline Desktop V1

This directory is an isolated desktop application. It does not replace the Laravel production application or Worker V5.2.

Locked architecture: one universal Windows installer, runtime hardware scanning, and a built-in Model Manager supporting Auto, Suggested, and Manual engine selection. Verified packages can be downloaded or imported offline, activated per tool, and safely removed. Engine metadata is data-driven so future hardware and model packages do not require rebuilding the main application.

Model downloads remain disabled until a model publisher URL and SHA-256 checksum are entered in `registry/engines.json`; the application never installs an unverified package. The local runner uses structured standard input and never invokes a command shell.

Windows Offline Voice uses the operating system's local SAPI voices and writes WAV output to `Documents/C-Net AI Studio/Outputs`. It requires no model download and no internet connection.

Whisper Multilingual Lite downloads a pinned Windows x64 runtime and multilingual model, verifies both SHA-256 digests, extracts them atomically, and creates `.srt` subtitles from selected WAV audio. Once installed, transcription is fully offline.

Run `npm test` for recommendation tests, `npm start` for development, and `npm run dist:win` on Windows to produce the NSIS installer.
