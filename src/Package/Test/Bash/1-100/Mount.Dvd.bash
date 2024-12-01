# normal mount
mount /dev/cdrom /mnt/Dvd


# corrupted disks
mount -t iso9660 /dev/sr0 /mnt/Dvd
mount -o ro,relatime,errors=continue /dev/sr0 /mnt/Dvd