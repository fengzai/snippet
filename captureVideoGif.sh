# $1 video $2 start time second $3 time length second
ffmpeg -i $1 -ss $2 -t $3 -pix_fmt rgb24 -loop 0 -f gif out.gif
