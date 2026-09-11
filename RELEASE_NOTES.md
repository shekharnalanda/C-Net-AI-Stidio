# C-Net AI Studio Universal Offline 1.0.2

Corrective production release of the hardware-adaptive Windows desktop application.

## Corrective fix

- Text generation now closes the llama.cpp input stream so it cannot wait for interactive input.
- Removed a command option unsupported by the pinned llama.cpp Windows runtime.
- Output is limited for practical CPU performance on lower-spec Windows PCs.
- A six-minute safety timeout stops an engine cleanly instead of leaving the workbench stuck indefinitely.
- Existing downloaded runtimes, models, projects, and preferences remain available after upgrading from 1.0.0.

## Included

- Universal Windows installer and portable application.
- Automatic hardware scan with Auto, Suggested, and Manual engine selection.
- Built-in Model Manager with verified install, update, switch, offline import, and safe removal workflows.
- Offline Hindi/English text and script generation with llama.cpp and Qwen2.5.
- Offline Windows voice generation and multilingual Whisper caption/transcription.
- Offline image generation profiles for CPU, Vulkan, and NVIDIA CUDA.
- Offline video composition with images, soundtrack, SRT subtitles, and MP4 export.
- Project history, image/video gallery, backup and verified restore.
- Device-bound signed-license foundation and tamper-evident usage records.
- Privacy-redacted diagnostics, crash recovery, strict renderer isolation, and secure ZIP extraction.

## Installation

Windows 10/11 64-bit is required. The installer itself does not bundle multi-gigabyte AI models. On first use, the application scans the PC and offers only compatible, pinned packages. Every automatic runtime/model download is HTTPS and SHA-256 verified before activation.

Lower-spec computers can use text, voice, captions, and video composition profiles. Image generation requires substantially more RAM and may be slow on CPU-only systems.

## Integrity

Every downloadable EXE is published with a matching `.sha256` file in this release. Microsoft-trusted Authenticode signing is not included until an organization code-signing certificate is configured.

## Preservation

This desktop release is isolated from `studio.mciedu.com`. It does not modify the production Laravel website, database, cPanel deployment, or Dedicated Worker V5.2.
