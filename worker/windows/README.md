# C-Net AI Studio Dedicated Worker V4

This worker performs heavy processing outside shared hosting.

Supported foundation:

- Secure bearer-token registration
- Automatic CPU/GPU/RAM/disk detection
- FFmpeg capability detection
- Worker heartbeat
- Distributed job pickup
- Progress reporting
- Job completion
- Failure reporting
- Automatic retry support
- Text-to-video pipeline foundation
- Image-to-video pipeline foundation
- Business ad rendering foundation
- Reel rendering foundation

## Architecture

C-Net AI Studio Web
        |
        | HTTPS Worker API
        v
Dedicated Worker
        |
        +-- FFmpeg
        +-- Local AI engines (future)
        +-- Image engines (future)
        +-- Speech engines (future)
        +-- Local LLM (future)

The web server does not need to perform heavy AI rendering.
