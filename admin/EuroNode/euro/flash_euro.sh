#! /bin/bash
systemctl stop pistar-watchdog.service
systemctl stop dstarrepeater.service
systemctl stop pistar-watchdog.timer
systemctl stop dstarrepeater.timer
systemctl stop mmdvmhost.timer
systemctl stop mmdvmhost.service

sudo mount -o remount,rw /

# firmware received in zip-format, unzip and continue
UPLOADED=./euro/*.zip
for zipped in $UPLOADED
do
       sudo unzip -o ${zipped} -d ./euro
done


FIRMWARE=./euro/*.hex
for found in $FIRMWARE
do
  echo "Found $found firmware..."
  # take action on this file, upload it to radioboard.
  sudo stm32flash -v -w ${found} /dev/ttyAMA0 -R -i 200,-3,3:-200,-3,3

  # Make a backup of the uploaded FW to backup-folder, and reboot afterwards.
  sudo mv ${found} euro/backup
  sudo mv ./euro/*.zip ./euro/backup
  # sudo reboot
done
