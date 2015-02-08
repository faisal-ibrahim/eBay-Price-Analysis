#!/usr/bin/env python
#!/usr/bin/python
from __future__ import division

# The next 4 line gives PHP permission to use matplotlib in python.
import os
os.environ[ 'MPLCONFIGDIR' ] = '/tmp'
import matplotlib
matplotlib.use('agg')

import json
import csv
import sys
import math
import numpy as np
import matplotlib.pyplot as plt

from subprocess import call
from numpy import *
#call(['whoami'])
priceColumn = 15

def bootstrap(dataSet):
    arr = []
    n = len(dataSet)
    i = 0
    while i < n:
        arr = arr + [random.choice(dataSet)]
        i = i + 1
    return arr

def getStatistics(dataSet):
    d = {}
    d["n"]      = len(dataSet)
    d["average"]= round(mean(dataSet), 2)
    d["median"] = round(median(dataSet), 2)
    d["stdev"]  = round(std(dataSet), 2)
    d["mini"]   = round(amin(dataSet), 2)
    d["maxi"]   = round(amax(dataSet),2)

    pcTile = np.int_(percentile(dataSet, [10, 20, 30, 40, 50, 60, 70, 80, 90]))
    d["p10"] = pcTile[0]
    d["p20"] = pcTile[1]
    d["p30"] = pcTile[2]
    d["p40"] = pcTile[3]
    d["p50"] = pcTile[4]
    d["p60"] = pcTile[5]
    d["p70"] = pcTile[6]
    d["p80"] = pcTile[7]
    d["p90"] = pcTile[8]
    return d

# Load prices
#try:
#    data = json.loads(sys.argv[1])
#except:
#    print "Error passing JSON from PHP to Python"
#    sys.exit(1)

totalPrice = []
entryNumber =[]
with open('data.csv', 'rb') as csvfile:
	line = csv.reader(csvfile)
	line.next()
	i = 1
	for row in line:
		entryNumber = entryNumber + [i]
		totalPrice = totalPrice + [float(row[priceColumn])] # column 15th of data.csv contains prices
		i = i+1
totalPrice.reverse() # Reverse: make most recent data points with the largest index
n = len(totalPrice)

# order statistics
stats = getStatistics(totalPrice)



# Price vs time
plt.figure(1)
#plt.subplot(211)
plt.plot(entryNumber, totalPrice, 'go')
plt.xlabel('Time')
plt.ylabel('Price')
plt.title('Price Trend')
plt.grid(True, 'both')
plt.axis([0, n, 0, 1.3*stats["maxi"]])
plt.savefig('plot.png')

# Histogram
plt.clf()
plt.figure(1)
#plt.subplot(212)
m, bins, patches = plt.hist(totalPrice, 10, (stats["p10"], stats["p90"]))
plt.xlabel('Price Bins')
plt.ylabel('Data Counts')
plt.title('Histogram of Price')
plt.grid(True, 'both')
#scale price bin width relative to stdev
i=1.5
low = math.floor(bins[0])
high = math.ceil(bins[-1])
if low < 0:
    low = mini
plt.axis([low, high, 0, 1.3*max(m)])
plt.savefig('histogram.png')


#bootstrapping histogram
meansArr = []
for i in range(2000):
    meansArr = meansArr + [mean(bootstrap(totalPrice))]
plt.clf()
plt.figure(1)
m, bins, patches = plt.hist(meansArr, 20)
low = math.floor(bins[0])
high = math.ceil(bins[-1])
plt.axis([low, high, 0, 1.3*max(m)])
plt.xlabel('Resampled Average Price')
plt.ylabel('Counts')
plt.title('Variance of the means (bootstrap resample)')
plt.grid(True, 'both')
plt.savefig('bootstrap.png')


#Use JSON to pass back to PHP
print json.dumps(stats) # send it to stout (to PHP)


