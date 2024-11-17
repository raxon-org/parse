for file in *.rar; do
  # Using 'rar' (if available) for extraction
  rar x -y -p- -ad "$file" "/path/to/output/"
done

/*
 cd /mnt/Disk2/Vsti/ && for file in *.rar; do
  rar x -y -p- -ad "$file" "/mnt/Disk2/Media/Software/Extract/"
done > /mnt/Disk2/Media/Software/Extract/Unrar.log
*/
