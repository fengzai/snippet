#ffmpeg -i $1 -an -vcodec libx264 -b 560k -pass 1 -f mp4 -y out.mp4
ffmpeg -i $1 -acodec copy -vcodec libx264 -b 560k -s 960x640 -f mp4 out.mp4
