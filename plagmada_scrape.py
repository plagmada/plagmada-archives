from urllib.request import urlopen
from urllib.error import HTTPError
from functools import wraps
from bs4 import BeautifulSoup
import time
import os
import re

# Defining an album scraping function.
# --
# parseAlbum() initializes necessary list variables:
# album[], album_notes[] & album_size[]
#
# It then grabs each column from the album table on the page and accesses the link to a deeper album.
# Those pages are either another album of albums, in which case the function detects this and calls itself
# or it's an album of images, in which case it calls parseItem(), which parses albums of individual
# images.  
#
# The deepest the recursion goes, BTW, is three levels deep.

deadlinks=[]

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
	# main_g2_ItemID refers to the php tag that is used in page links.  g2_* is used throughout the Gallery2 markup.
	# I'll try to use the specific tag from there when appropriate.

	for i in range(0,len(album)):
		print("i Loop position is {0}".format(i))
		main_g2_itemId = album[i].split('&')
		album_page = urlopen_with_retry ("http://plagmada.org/gallery/{0}".format(main_g2_itemId[0]))
		if album_page:
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
                                    print("	<--- BACK TO REALITY")
                    else:
                            print("Size: EMPTY")
                    
                    # Before we enter the parseItem function, we want to know how many item pages exist for
                    # a particular album.  That way, we can collect the images and data from each.
                    # the g2_page for-loop collects each of the item pages and then runs parseItem on them.
                    # the g2_ItemId[] list is also used here to stay in the correct gallery

                    pageLinks=[]
                    blockCorePager = alSoup.findAll("div", {"class":"block-core-Pager"})	
                    if blockCorePager:
                            g2_itemId = main_g2_itemId[0].split('=')
                            pageLinks = blockCorePager[0].findAll("a")
                            print("---\\---")
                            if pageLinks:
                                    pageCount = pageLinks[-1].text.strip()
                                    print("pageCount: {0}".format(int(pageCount)))
                                    for g2_page in range(1,(int(pageCount)+1)):
                                            print("g2_page: {0} ".format(g2_page))
                                            current_page = urlopen_with_retry ("http://plagmada.org/gallery/main.php?g2_itemId={0}&g2_page={1}".format(g2_itemId[1],g2_page)) 	
                                            if current_page:
                                                pageSoup = BeautifulSoup(current_page.read(), "html.parser")
                                                parseItem( pageSoup )
                                                print("HELLO!")

                            else:
                                    print("ONLY ONE PAGE IN THIS GALLERY")
                                    one_page = urlopen_with_retry ("http://plagmada.org/gallery/main.php?g2_itemId={0}&g2_page=1".format(g2_itemId[1]))
                                    if one_page:
                                        oneSoup = BeautifulSoup(one_page.read(), "html.parser")
                                        parseItem( oneSoup )
                    else:
                            print("EMPTY PAGE")

                    print("---||---")

# The parseItem function reads a page of links to individual gallery objects, i.e. individual scans.  It then collects the desired metadata and
# the highest resolution version of the image and saves it locally.

def parseItem( oneItem ):
	print("Made it this far")
	speck=[]
	itemList = oneItem.findAll("td", {"class":"giItemCell"})
	galleryDescription = oneItem.findAll("p", {"class":"giDescription"})
	for itemSpeck in itemList:
		speck.append(itemSpeck.a.get('href'))
		
	if galleryDescription:	
		print(galleryDescription[0].get_text().strip())
	else:
		print("NO DESCRIPTION")
	
	for i in range(0,len(speck)):
		main_g2_itemId = speck[i].split('&')
		item_page = urlopen_with_retry ("http://plagmada.org/gallery/{0}".format(main_g2_itemId[0]))
		if item_page:
                    itSoup = BeautifulSoup(item_page.read(), "html.parser")

                    # Get Name
                    item_name = itSoup.h2.get_text().strip()
                    print(item_name)
                    
                    # Get Detailed Description (if any)
                    itemDescription = itSoup.findAll("p", {"class":"giDescription"})
                    if itemDescription:
                            print(itemDescription[0].get_text().strip())	
                    else:
                            print("NO ITEM DESCRIPTION")

                    #get date
                    item_date = itSoup.findAll("div", {"class":"date summary"})
                    print(item_date[0].get_text().strip())

                    #get largest size
                    sizeBlock = itSoup.findAll("div", {"class":"block-core-PhotoSizes giInfo"})
                    largestSize = sizeBlock[0].get_text().strip().split(" ")
                    print(largestSize[-1].strip())

                    #get largest image
                    #Each image has its own itemId number.  This isn't available on the default page, though, because Gallery2 is a 
                    #dumpster fire in some ways.  Maybe it's just +1 to the main itemId number, but I'm not going to count on that
                    #so I need to hop over to the page that displays the highest resolution image and grab the link to it from there.
                    #KHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAN!!

                    highres_page = urlopen_with_retry ("http://plagmada.org/gallery/{0}&g2_imageViewsIndex=1".format(main_g2_itemId[0]))
                    highresSoup = BeautifulSoup(highres_page.read(), "html.parser")
                    # And then we'll grab the image...	

                    print("---//---")

def retry(ExceptionToCheck, tries=4, delay=3, backoff=2, logger=None):
	"""Retry calling the decorated function using an exponential backoff.

	http://www.saltycrane.com/blog/2009/11/trying-out-retry-decorator-python/
	 original from: http://wiki.python.org/moin/PythonDecoratorLibrary#Retry

	 :param ExceptionToCheck: the exception to check. may be a tuple of exceptions to check
	 :type ExceptionToCheck: Exception or tuple
	 :param tries: number of times to try (not retry) before giving up
	 :type tries: int
	 :param delay: initial delay between retries in seconds
	 :type delay: int
	 :param backoff: backoff multiplier e.g. value of 2 will double the delay each retry
	 :type backoff: int
	 :param logger: logger to use. If None, print
	 :type logger: logging.Logger instance
	 """

	def deco_retry(f):

		@wraps(f)
		def f_retry(*args, **kwargs):
			global deadlinks
			mtries, mdelay = tries, delay
			while mtries > 1:
				try:
					return f(*args, **kwargs)
				except ExceptionToCheck as e:
					msg = str(e) + " - Retrying in %d seconds..." % (mdelay)
					if logger:
						logger.warning(msg)
					else:
						print(msg)
					time.sleep(mdelay)
					mtries -= 1
					if mtries == 1:
						msg = str(e) + " - persists after multiple retries.  Bypassing link: " + str(args[0])
						print(msg)
						deadlinks.append(str(args[0]))
						return	
					mdelay *= backoff

			return f(*args, **kwargs)

		return f_retry  # true decorator

	return deco_retry

knownExceptions = (HTTPError, TimeoutError, ConnectionResetError) 
@retry(knownExceptions, tries=4, delay=3, backoff=2)
def urlopen_with_retry(URL):
    return urlopen(URL)

# Initiating the Scrape from the top level PlaGMaDA album page

#PlaGMaDA = urlopen_with_retry("http://plagmada.org/gallery/main.php?g2_itemId=1561")
PlaGMaDA = urlopen_with_retry("http://plagmada.org/gallery/main.php")

if PlaGMaDA:
	mainSoup = BeautifulSoup(PlaGMaDA.read(), "html.parser")

	parseAlbum( mainSoup )

print("These are the dead links: ")
for x in range(len(deadlinks)):
		print(deadlinks[x])

# Just checking the functionality...
# os.mkdir("test")
