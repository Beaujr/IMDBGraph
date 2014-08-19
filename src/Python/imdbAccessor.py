__author__ = 'beaurudder'
#!python
import sys
import json

from imdb import IMDb
ia = IMDb('sql', uri='mysql://root:root@localhost/imdb')

if str(sys.argv[1]) == '-sm':
    search_sk = ia.search_movie(str(sys.argv[2]))
    # for key, value in search_sk[0].items() :
    #     print (key, value)
    films = []
    for result in search_sk :

    	film = ia.get_movie(result.movieID)
    	D = {}
    	D['title'] = result['long imdb title']
    	D['id'] = result.movieID
    	films.append(D)
    print json.dumps(films)


# Print it
# print ("The total numbers of args passed to the script: %d " % total)
# print ("Args list: %s " % cmdargs)

#if cmdargs[1] == '-m':
if str(sys.argv[1]) == '-gm':
    #print json.dumps(films)
    search_sF = ia.get_movie(str(sys.argv[2]))
    SearchResults = {}
    SearchResults['id'] = sys.argv[2]
    SearchResults['title'] = search_sF.data.get('title')
    people = []
    for cast in search_sF['cast'] :
        #print cast.personID, cast
        search_pK = ia.get_person(cast.personID)
        thespian = {}
        thespian['name'] = cast.data['name']
        thespian['id'] = cast.personID
        filmography = []
        try:
            for m in ia.get_person(cast.personID).data.get('actor') :
                filmography.append(m.data['title'])
                filmography.append(m.movieID)
        except TypeError:
            pass
        try:
            for m in ia.get_person(cast.personID).data.get('actress') :
                filmography.append(m.data['title'])
                filmography.append(m.movieID)
        except TypeError:
            pass

        thespian['films'] = filmography
        people.append(thespian)
    SearchResults['people'] = people
    print json.dumps(SearchResults)
    #search_sk = ia.search_person(u'Tobey Maguire')
if str(sys.argv[1]) == '-lm':
    #print json.dumps(films)
    movies = sys.argv[2].split(',')
    SearchResults = {}
    Films = []
    ActualFilms = []
    Actors = []
    for movie in movies:
        search_sF = ia.get_movie(str(movie))
        MovieResult = {}
        MovieResult['id'] = movie
        MovieResult['title'] = search_sF.data.get('title')
        for cast in search_sF['cast'] :
            #print cast.personID, cast
            search_pK = ia.get_person(cast.personID)
            thespian = {}
            thespian['name'] = cast.data['name']
            thespian['id'] = cast.personID
            filmography = []
            try:
                for m in ia.get_person(cast.personID).data.get('actor') :
                    # filmography.append(m.data['title'])
                    filmography.append(m.movieID)
                    MovieResult = {}
                    MovieResult['id'] = m.movieID
                    MovieResult['title'] = m.data['title']
                    if MovieResult not in Films:
                        Films.append(MovieResult)
                    else:
                        if(MovieResult not in ActualFilms):
                            ActualFilms.append(MovieResult)
            except TypeError:
                pass
            try:
                for m in ia.get_person(cast.personID).data.get('actress') :
                    # filmography.append(m.data['title'])
                    filmography.append(m.movieID)
                    MovieResult = {}
                    MovieResult['id'] = m.movieID
                    MovieResult['title'] = m.data['title']
                    if(MovieResult not in Films):
                        Films.append(MovieResult)
                    else:
                        if(MovieResult not in ActualFilms):
                            ActualFilms.append(MovieResult)
            except TypeError:
                pass

            thespian['films'] = filmography
            Actors.append(thespian)
        SearchResults['Actors'] = Actors
        SearchResults['Films'] = ActualFilms
        print json.dumps(SearchResults)
    #search_sk = ia.search_person(u'Tobey Maguire')

#['biography', 'episodes', 'filmography', 'main', 'other works']