ffmpeg -f alsa -i default -f v4l2 -s 1024x768 -i /dev/video0 -acodec flac -vcodec ffvhuff out.mkv
