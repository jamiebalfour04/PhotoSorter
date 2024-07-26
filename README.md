# BalfPhotoSorter
A simple photo sorting application that does it all.

Use this program to quickly sort through thousands of photos quickly. It simply allows you to quickly allocate photos to folders by clicking on the name of the folder. 

Set the config file to the folder you wish to sort. 

_You'll need a webserver running on your system to run this._

**If you wish to sort heic photos, include a symblink or a copy of the magick library in the same folder.**

If you're using Windows and MAMP, put the code for this in the root directory or some easily accessible directory (e.g. C:\MAMP\htdocs\photos\). 
You'll also need the Image Magick library. Again, if you are using Windows, download it first from [here](https://imagemagick.org/script/download.php#windows) and put the binary into the same folder as the code. Rename the binary to **magick.exe**. 

If you are using macOS or any *nix based OS, you can use a symblink to the Image Magick library, just ensure the symblink is named **magick**. 
