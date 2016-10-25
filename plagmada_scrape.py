from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
import os
import re


PlaGMaDA = urlopen("http://plagmada.org/gallery/main.php")
mainSoup = BeautifulSoup(PlaGMaDA.read(), "html.parser")

# galleryList
# This seems to be the optimal way to collect gallery links.  It skips the link back to the introduction page and
# since that's the only unwanted table cell, this is much easier than trying to play with the sibling function.

album=[]
album_notes=[]
album_size=[]
galleryList = mainSoup.findAll("td", {"class":"giAlbumCell gcBackground1"})
for galleryItem in galleryList:
	album.append(galleryItem.a.get('href'))
	album_notes.append(galleryItem.find('p', class_="giDescription"))
	album_size.append(galleryItem.find('div', class_="size summary"))

# This will iterate through each album page.  Ultimately, it will then iterate through each item page for the album.
# Currently, it is grabbing the H2 header from the first page of the album and using regex to create a cromulent
# directory name for each album.  
#
# g2_ItemID refers to the php tag that is used in page links.  g2_* is used throughout the Gallery2 markup.
# I'll try to use the spefiic tag from there when appropriate.


for i in range(0,len(album)):
	g2_itemId = album[i].split('&')
	album_page = urlopen ("http://plagmada.org/gallery/{0}".format(g2_itemId[0]))
	alSoup = BeautifulSoup(album_page.read(), "html.parser")
	
	# Album Titles
	album_title = alSoup.h2.get_text()
	album_directory = re.sub(r' ',"_",album_title.strip())
	album_directory = re.sub(r'\,',"",album_directory)
	album_directory = re.sub(r'\"',"",album_directory)
	print(album_directory)
	if album_notes[i]:
		print(album_notes[i].text.strip())
	print(album_size[i])
	print("---")

# Just checking the functionality...
# os.mkdir("test")
