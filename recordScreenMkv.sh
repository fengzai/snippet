ffmpeg -f x11grab -r 30 -i :0.0 -f alsa -i hw:0,0 -acodec flac -vcodec ffvhuff out.mkv
