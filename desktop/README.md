# C-Net AI Studio Offline Desktop V11

This directory is an isolated desktop application. It does not replace the Laravel production application or Worker V5.2.

Locked architecture: one universal Windows installer, runtime hardware scanning, and a built-in Model Manager supporting Auto, Suggested, and Manual engine selection. Verified packages can be downloaded or imported offline, activated per tool, and safely removed. Engine metadata is data-driven so future hardware and model packages do not require rebuilding the main application.

Model downloads remain disabled until a model publisher URL and SHA-256 checksum are entered in `registry/engines.json`; the application never installs an unverified package. The local runner uses structured standard input and never invokes a command shell.

Windows Offline Voice uses the operating system's local SAPI voices and writes WAV output to `Documents/C-Net AI Studio/Outputs`. It requires no model download and no internet connection.

Whisper Multilingual Lite downloads a pinned Windows x64 runtime and multilingual model, verifies both SHA-256 digests, extracts them atomically, and creates `.srt` subtitles from selected WAV audio. Once installed, transcription is fully offline.

Image Studio provides hardware-aware CPU, Vulkan, and NVIDIA CUDA profiles using pinned official `stable-diffusion.cpp` builds. The separately licensed Stable Diffusion 1.5 model is downloaded only after license confirmation and SHA-256 verification. Prompt, negative prompt, size, steps, and seed controls generate PNG files into the protected project output directory; the project gallery can export PNG/JPEG-compatible output without exposing arbitrary local files.

Video Studio composes selected images, an optional soundtrack, and optional SRT subtitles into MP4. Auto mode chooses the universal CPU encoder or NVIDIA hardware encoding according to the current PC. Duration and resolution are bounded, FFmpeg is invoked without a command shell, temporary manifests are removed, and completed videos can be previewed or exported from the project gallery.

Commercial Desktop Foundation adds device-bound Ed25519 signed-license verification, Community/Commercial edition status, tamper-evident monthly usage records, persistent engine/language preferences, verified workspace backup and restore, and SHA-256 based runtime/model update detection. Pricing and blocking limits remain policy-driven and are not hard-coded into the universal installer.

Production QA & Security adds single-instance protection, a restrictive renderer Content Security Policy, blocked external navigation/windows, task-authorized IPC input validation, crash-safe project snapshots, rotating privacy-redacted support logs, and an exportable diagnostics report. The Windows pipeline runs dependency auditing and publishes a SHA-256 checksum beside every installer artifact.

Run `npm test` for recommendation tests, `npm start` for development, and `npm run dist:win` on Windows to produce the NSIS installer.
