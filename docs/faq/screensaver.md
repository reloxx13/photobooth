# Screensaver function

The screensaver keeps the start screen fresh and prevents burn‑in when guests are idle. Configure it in the Admin Panel under **General → Screensaver**.

## What it can show

- **Image**: Display a single image. Use `Screensaver image path`.
- **Video**: Loop a single video. Use `Screensaver video path`.
- **Folder**: Cycle through files in `private/screensavers/`. Supported: `.jpg`, `.png`, `.gif`, `.mp4`, `.webm`.
- **Gallery**: Cycle through gallery photos guests already took; falls back to the image path if the gallery is empty.

## Text overlay

- Optional **Screensaver text** shows on top of the media.
- Choose **Text position** (`top`, `bottom`, `center`) to avoid covering key visuals.
- In Gallery mode the overlay alternates between your text and event text to avoid screen burn.

## Timing

- **Timeout before screensaver**: minutes of no interaction before it starts. Set to `0` to keep the screensaver off even if enabled.
- **Slide change interval**: minutes between media switches in Folder or Gallery mode. Has no effect for single image/video modes.
- Any click or touch hides the screensaver and resets the timer.
