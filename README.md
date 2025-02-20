# Simple Hi-anime Player

A lightweight PHP anime player using Vidstack, designed for a custom `hianime-api` endpoint.

## How to Use
1. **Deploy the API**: Deploy an API compatible with `/api/v2/hianime/episode/sources` (e.g., a fork of [aniwatch-api](https://github.com/ghoshRitesh12/aniwatch-api) modified to match this endpoint).
2. **Set Your API URL**:
   - Open `player.php`.
   - Add `$myapi_url = "https://your-api-here.vercel.app";` with your deployed API URL.
3. **Host It**: Upload to a PHP server (e.g., XAMPP, web host).
4. **Access**: `http://your-site/player.php?id=<animeEpisodeId>&server=hd-1&category=sub`.

## Query Parameters
| Parameter       | Type   | Description                              | Required? | Default |
|-----------------|--------|------------------------------------------|-----------|---------|
| animeEpisodeId  | string | The unique anime episode id.             | Yes       | --      |
| server          | string | The name of the server.                  | No        | "hd-1"  |
| category        | string | The category of the episode ('sub', 'dub' or 'raw'). | No    | "sub"   |

- **Example 1**: `http://localhost/player.php?id=jujutsu-kaisen-2nd-season-18413?ep=102662`  
  or `http://localhost/player.php?id=jujutsu-kaisen-2nd-season-18413?ep=102662&server=hd-1&category=sub`
- **Example 2**: `https://yourdomain.com/player.php?id=jujutsu-kaisen-2nd-season-18413?ep=102662&server=hd-1&category=dub`

## Requirements
- PHP 7+ with `file_get_contents` enabled.
- An API with the endpoint `/api/v2/hianime/episode/sources?animeEpisodeId={id}&server={server}&category={dub || sub || raw}`.

## Features
- Streams anime via HiAnime API.
- Supports JSON-formatted responses.
- Plays episodes with m3u8 links.
- Includes subtitles and thumbnails.
- Features chapter support.

## Notes
- This player is tailored to a custom API endpoint. The original `aniwatch-api` uses `/anime/episode/source`—adjust `$api_url` in `player.php` if your API differs.

## How It Works (For Beginners)
This is a simple PHP script that:
1. Takes an episode ID (like `jujutsu-kaisen-2nd-season-18413?ep=102662`) from the URL.
2. Fetches video data from your API (e.g., `https://your-api.vercel.app/api/v2/hianime/episode/sources`).
3. Displays the video with subtitles and skip buttons using Vidstack Player.
- **Newbies**: Upload `player.php` to a PHP server (like XAMPP), edit the API URL, and open it in your browser with an episode ID. That’s it!

## CLI Setup (For Nerds)
Want to grab this via command line? Here’s how:
```bash
# Clone the repo
git clone https://github.com/your-username/simple-hianime-player.git

# Navigate into the folder
cd simple-Hi-anime-Player

# Edit the API URL in player.php with your favorite editor (e.g., nano or VSCode)
nano player.php  # Replace $myapi_url with your API URL

# Run it locally with PHP’s built-in server
php -S localhost:8000

# Open in browser: http://localhost:8000/player.php?id=jujutsu-kaisen-2nd-season-18413?ep=102662
