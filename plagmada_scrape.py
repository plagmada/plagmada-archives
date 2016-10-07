from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
plagmada = urlopen("http://plagmada.org/gallery/main.php")
plagSoup = BeautifulSoup(plagmada.read(), "html.parser")
galleryList = plagSoup.findAll("td", {"class":"giAlbumCell gcBackground1"})
for gallery in galleryList:
	print(gallery.a)
