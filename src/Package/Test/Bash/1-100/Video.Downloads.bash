 youtube-dl -v -x --restrict-filenames --prefer-ffmpeg https://www.youtube.com/watch?v=JGwWNGJdvx8 > /tmp/youtube-dl.log
 youtube-dl --list-formats
 yt-dlp -f webm --restrict-filenames --prefer-ffmpeg https://www.youtube.com/watch?v=JGwWNGJdvx8  2>&1 >> /tmp/youtube-dl.log