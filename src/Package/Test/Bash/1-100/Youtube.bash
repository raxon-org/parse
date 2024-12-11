/*
            $execute = '
            wat admin task "
                cd ' . $target_directory . ' &&
                youtube-dl -v -x --restrict-filenames --audio-format mp3 --prefer-ffmpeg 2>&1 ' . $object->request('node.url') . ' > ' . $url . '
            " ' . $token . '
        ';
            */
            $execute = '
            app admin task "
                cd ' . $target_directory . ' &&
                yt-dlp -x --restrict-filenames --audio-format mp3 --prefer-ffmpeg ' . $object->request('node.url') . '  2>&1 >> ' . $url . '
            " ' . $token;

            /*

            $execute = '
            app admin task "
                cd ' . $target_directory . ' &&
                yt-dlp -f webm --restrict-filenames --prefer-ffmpeg ' . $object->request('node.url') . '  2>&1 >> ' . $url . '
            " ' . $token;


             --list-formats

             -f <id> for lower quality < 2 GB

                ffmpeg -i input.mp4 output.webm

            */
