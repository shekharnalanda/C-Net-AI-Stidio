# Runtime sources and commercial safety

The application does not silently download an unverified executable.

- Text adapter: Ollama local API. The packaged registry currently detects an existing local runtime; its official Windows package is not auto-installed until the large package and model download flow has its own confirmation screen.
- Captions adapter target: `ggml-org/whisper.cpp`, MIT licensed. The official Windows x64 runtime release `b4938` publishes SHA-256 `c2a4b60edb11f7e11a9191ffb50929535527d4d91c9903dbe3e554583bbbc63d` for `whisper-bin-x64.zip`. A language model is still required, so this runtime is not marked installable yet.
- Voice adapter: Windows System.Speech/SAPI supplied by Windows. It works offline and does not add a paid AI API dependency.
- Media conversion: pinned BtbN FFmpeg `n8.1.2-51-g7ba069f4f1` Windows x64 shared build under LGPL-2.1-or-later, verified by SHA-256 before extraction.

Every downloadable runtime/model must have an HTTPS source, fixed version, SHA-256 digest, license record, and compatibility metadata before its Install button is enabled.
