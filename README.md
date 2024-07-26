# BalfPhotoSorter
A simple photo sorting application that does it all.

Use this program to quickly sort through thousands of photos quickly. It simply allows you to quickly allocate photos to folders by clicking on the name of the folder. 

Set the config file to the folder you wish to sort. 

_You'll need a webserver running on your system to run this._

If you're using Windows and MAMP, put the code for this in the server root directory or some easily accessible directory (e.g. C:\MAMP\htdocs\photos\). If you are using macOS with MAMP this would be /Applications/MAMP/htdocs/photos/.

**If you wish to sort HEIC photos (i.e. those that are taken on an iOS device), you'll need the Image Magick library.**

## Image Magick library ##

If you are using Windows, download Image Magick first from [here](https://imagemagick.org/script/download.php#windows) and put the binary into the same folder as the code. Rename the binary to **magick.exe**. 

If you are using macOS or any *nix based OS, you can use a symblink to the Image Magick library, just ensure the symblink is named **magick**. Alternatively, follow the instructions [here](https://imagemagick.org/script/download.php#macosx) to install Image Magick.
