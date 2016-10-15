from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
import os
plagmada = urlopen("http://plagmada.org/gallery/main.php")
plagSoup = BeautifulSoup(plagmada.read(), "html.parser")

# galleryList
# This seems to be the optimal way to collect gallery links.  It skips the link back to the introduction page and
# since that's the only unwanted table cell, this is much easier than trying to play with the sibling function.

album=[]
galleryList = plagSoup.findAll("td", {"class":"giAlbumCell gcBackground1"})
for gallery in galleryList:
	album.append(gallery.a.get('href'))

# Presently, this loads the first page of each album into Beautful Soup and prints out the H2 heading
# H2 contains album names, so this is useful info.  Will likely use this to create directories for each album

for i in range(0,len(album)):
	g2_itemId = album[i].split('&')
	#print(g2_itemId[0])
	album_page = urlopen ("http://plagmada.org/gallery/{0}".format(g2_itemId[0]))
	alSoup = BeautifulSoup(album_page.read(), "html.parser")
	# print(alSoup.h2)
	album_name = str(alSoup.h2)
	the_bits = album_name.split()
	print("{}_{}".format(the_bits[1],the_bits[2]))

# Just checking the functionality...
# os.mkdir("test")
