from urllib.request import urlopen
from urllib.error import HTTPError
from bs4 import BeautifulSoup
import os
import re


# Initiating the Scrape from the top level PlaGMaDA album page

PlaGMaDA = urlopen("http://plagmada.org/gallery/main.php")
mainSoup = BeautifulSoup(PlaGMaDA.read(), "html.parser")

# Defining an album scraping function.
# --
# parseAlbum() initializes necessary list variables:
# album[], album_notes[] & album_size[]
#
# It then grabs each column from the album table on the page and accesses the link to a deeper album.
# Those pages are either another album of albums, in which case the function detects this and calls itself
# or it's an album of images, in which case it calls [UNBUILT FUNCTION HERE] that parses albums of individual
# images.  
#
# The deepest the recursion goes, BTW, is three levels deep.

def parseAlbum( onePage ):
	album=[]
	album_notes=[]
	album_size=[]
	galleryList = onePage.findAll("td", {"class":"giAlbumCell gcBackground1"})
	for galleryItem in galleryList:
		album.append(galleryItem.a.get('href'))
		album_notes.append(galleryItem.find('p', class_="giDescription"))
		album_size.append(galleryItem.find('div', class_="size summary"))

	# In the loop below, we are grabbing the H2 header from the current album page and using regex to create a cromulent
	# directory name for each album.  
	#
	# g2_ItemID refers to the php tag that is used in page links.  g2_* is used throughout the Gallery2 markup.
	# I'll try to use the specific tag from there when appropriate.

	for i in range(0,len(album)):
		print("i Loop position is {0}".format(i))
		g2_itemId = album[i].split('&')
		album_page = urlopen ("http://plagmada.org/gallery/{0}".format(g2_itemId[0]))
		alSoup = BeautifulSoup(album_page.read(), "html.parser")
	
		# Album Titles Made Cromulent for Filesystem Use
		album_title = alSoup.h2.get_text()
		album_directory = re.sub(r' ',"_",album_title.strip())
		album_directory = re.sub(r'\,',"",album_directory)
		album_directory = re.sub(r'\"',"",album_directory)
		print(album_directory)
		if album_notes[i]:
			print(album_notes[i].text.strip())
		else:
			print("NO NOTES")
		if album_size[i]:
			album_items = album_size[i].text.strip().split()
			item_number = album_items[1]
			print(item_number)
			if len(album_items) > 3:
				album_items[3] = re.sub(r'\(',"",album_items[3])
				print("---> ALBUMCEPTION DETECTED <---")
				parseAlbum(alSoup)
				print("   <--- BACK TO REALITY")
		else:
			print("Size: EMPTY")
		print("---")

parseAlbum(mainSoup)

# Just checking the functionality...
# os.mkdir("test")
