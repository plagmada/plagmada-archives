from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
plagmada = urlopen("http://plagmada.org/gallery/main.php")
plagSoup = BeautifulSoup(plagmada.read(), "html.parser")

# galleryList
# This seems to be the optimal way to collect gallery links.  It skips the link back to the introduction page and
# since that's the only unwanted table cell, this is much easier than trying to play with the sibling function.

album=[]
galleryList = plagSoup.findAll("td", {"class":"giAlbumCell gcBackground1"})
for gallery in galleryList:
	album.append(gallery.a.get('href'))

for i in range(0,len(album)):
	print(album[i])
