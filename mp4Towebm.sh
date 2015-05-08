#mp4 to webm
ffmpeg -y -i $1 -f webm -vcodec libvpx -acodec libvorbis -vb 1600000 out.webm
