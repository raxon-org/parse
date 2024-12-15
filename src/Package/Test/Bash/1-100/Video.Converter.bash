ffmpeg -i GOPR1260.MP4 -vf yadif -c:v libx264 -threads 4 -crf 18 -r 25 Ferrari.mp4 (#mp4 only no webm)

ffmpeg -i VTS_01_1.VOB -vf yadif -c:v libvpx-vp9 -crf 18 -b:v 0 -threads 4 -r 25 -c:a libvorbis vts_o1_1.webm (#webm only no mp4)