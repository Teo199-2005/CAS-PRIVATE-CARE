// Shared NY Location Data Composable
import { ref, computed } from 'vue';

const nyCounties = ref([]);
const countyCityMap = ref({});

export function useNYLocationData() {
  const loadNYLocationData = async () => {
    // Set fallback data immediately with all 62 NY counties
    const fallbackData = {
      "Albany": ["Albany", "Cohoes", "Watervliet", "Green Island", "Menands", "Colonie", "Guilderland", "Bethlehem"],
      "Allegany": ["Wellsville", "Cuba", "Belmont", "Bolivar", "Richburg", "Scio", "Friendship", "Angelica"],
      "Bronx": ["Bronx", "Fordham", "Riverdale", "Throggs Neck", "Pelham Bay", "Concourse", "Morrisania"],
      "Broome": ["Binghamton", "Johnson City", "Endicott", "Vestal", "Conklin", "Chenango", "Kirkwood"],
      "Cattaraugus": ["Olean", "Salamanca", "Little Valley", "Ellicottville", "Franklinville", "Portville"],
      "Cayuga": ["Auburn", "Moravia", "Fair Haven", "Weedsport", "Union Springs", "Cato", "Meridian"],
      "Chautauqua": ["Jamestown", "Dunkirk", "Fredonia", "Westfield", "Mayville", "Silver Creek"],
      "Chemung": ["Elmira", "Horseheads", "Big Flats", "Elmira Heights", "Millport", "Van Etten"],
      "Chenango": ["Norwich", "Oxford", "Greene", "Sherburne", "Afton", "Bainbridge", "Sidney"],
      "Clinton": ["Plattsburgh", "Champlain", "Rouses Point", "Dannemora", "Altona", "Chazy"],
      "Columbia": ["Hudson", "Kinderhook", "Chatham", "Philmont", "Valatie", "Copake"],
      "Cortland": ["Cortland", "Homer", "McGraw", "Marathon", "Dryden", "Cincinnatus"],
      "Delaware": ["Delhi", "Oneonta", "Sidney", "Walton", "Margaretville", "Hancock"],
      "Dutchess": ["Poughkeepsie", "Beacon", "Rhinebeck", "Hyde Park", "Millbrook", "Red Hook"],
      "Erie": ["Buffalo", "Cheektowaga", "West Seneca", "Amherst", "Tonawanda", "Lackawanna"],
      "Essex": ["Elizabethtown", "Ticonderoga", "Westport", "Keene", "Lake Placid", "Wilmington"],
      "Franklin": ["Malone", "Saranac Lake", "Tupper Lake", "Fort Covington", "Bombay", "Constable"],
      "Fulton": ["Gloversville", "Johnstown", "Northville", "Broadalbin", "Mayfield", "Perth"],
      "Genesee": ["Batavia", "Le Roy", "Oakfield", "Alexander", "Byron", "Corfu"],
      "Greene": ["Catskill", "Coxsackie", "Cairo", "Hunter", "Tannersville", "Windham"],
      "Hamilton": ["Lake Pleasant", "Indian Lake", "Wells", "Speculator", "Long Lake", "Inlet"],
      "Herkimer": ["Herkimer", "Little Falls", "Ilion", "Mohawk", "Dolgeville", "Frankfort"],
      "Jefferson": ["Watertown", "Carthage", "Clayton", "Alexandria Bay", "Sackets Harbor", "Dexter"],
      "Kings": ["Brooklyn", "Bedford-Stuyvesant", "Park Slope", "Williamsburg", "DUMBO", "Bay Ridge"],
      "Lewis": ["Lowville", "Lyons Falls", "Croghan", "Constableville", "Harrisville", "Turin"],
      "Livingston": ["Geneseo", "Mount Morris", "Dansville", "Avon", "Caledonia", "Lima"],
      "Madison": ["Oneida", "Canastota", "Hamilton", "Chittenango", "Wampsville", "Morrisville"],
      "Monroe": ["Rochester", "Brighton", "Greece", "Irondequoit", "Webster", "Penfield"],
      "Montgomery": ["Amsterdam", "Fonda", "Canajoharie", "Palatine Bridge", "St. Johnsville"],
      "Nassau": ["Hempstead", "Long Beach", "Glen Cove", "Freeport", "Valley Stream", "Inwood"],
      "New York": ["Manhattan", "Upper East Side", "Upper West Side", "Greenwich Village", "SoHo", "Tribeca", "Chelsea", "Midtown", "Hell's Kitchen", "Times Square", "Financial District", "Lower East Side", "East Village", "Chinatown", "Little Italy", "Harlem", "Washington Heights", "Inwood", "Lenox Hill"],
      "Niagara": ["Niagara Falls", "North Tonawanda", "Lockport", "Lewiston", "Youngstown"],
      "Oneida": ["Utica", "Rome", "Sherrill", "Whitesboro", "New York Mills", "Oriskany"],
      "Onondaga": ["Syracuse", "Liverpool", "Baldwinsville", "East Syracuse", "North Syracuse"],
      "Ontario": ["Canandaigua", "Geneva", "Victor", "Phelps", "Shortsville", "Clifton Springs"],
      "Orange": ["Newburgh", "Middletown", "Port Jervis", "Goshen", "Warwick", "Monroe"],
      "Orleans": ["Albion", "Medina", "Holley", "Lyndonville", "Yates", "Kendall"],
      "Oswego": ["Oswego", "Fulton", "Mexico", "Pulaski", "Central Square", "Phoenix"],
      "Otsego": ["Cooperstown", "Oneonta", "Richfield Springs", "Cherry Valley", "Milford"],
      "Putnam": ["Carmel", "Cold Spring", "Brewster", "Mahopac", "Kent", "Putnam Valley"],
      "Queens": ["Queens", "Flushing", "Jamaica", "Astoria", "Long Island City", "Forest Hills"],
      "Rensselaer": ["Troy", "Rensselaer", "Hoosick Falls", "Berlin", "Grafton", "Petersburgh"],
      "Richmond": ["Staten Island", "St. George", "Tottenville", "New Brighton", "Port Richmond"],
      "Rockland": ["New City", "Spring Valley", "Nyack", "Suffern", "Pearl River", "Haverstraw"],
      "Saratoga": ["Saratoga Springs", "Ballston Spa", "Mechanicville", "Waterford", "Stillwater"],
      "Schenectady": ["Schenectady", "Rotterdam", "Niskayuna", "Glenville", "Duanesburg"],
      "Schoharie": ["Schoharie", "Cobleskill", "Middleburgh", "Richmondville", "Sharon Springs"],
      "Schuyler": ["Watkins Glen", "Montour Falls", "Burdett", "Odessa", "Tyrone", "Hector"],
      "Seneca": ["Waterloo", "Seneca Falls", "Ovid", "Lodi", "Romulus", "Varick"],
      "St. Lawrence": ["Canton", "Ogdensburg", "Potsdam", "Massena", "Gouverneur", "Norwood"],
      "Steuben": ["Corning", "Hornell", "Bath", "Canisteo", "Painted Post", "Wayland"],
      "Suffolk": ["Huntington", "Brookhaven", "Islip", "Babylon", "Smithtown", "Riverhead"],
      "Sullivan": ["Monticello", "Liberty", "Fallsburg", "Bloomingburg", "Wurtsboro"],
      "Tioga": ["Owego", "Waverly", "Spencer", "Candor", "Newark Valley", "Nichols"],
      "Tompkins": ["Ithaca", "Dryden", "Groton", "Trumansburg", "Lansing", "Newfield"],
      "Ulster": ["Kingston", "New Paltz", "Saugerties", "Ellenville", "Woodstock", "Rosendale"],
      "Warren": ["Glens Falls", "Lake George", "Warrensburg", "Bolton", "Chestertown"],
      "Washington": ["Hudson Falls", "Whitehall", "Cambridge", "Hoosick", "Salem", "Granville"],
      "Wayne": ["Lyons", "Newark", "Clyde", "Palmyra", "Sodus", "Wolcott"],
      "Westchester": ["White Plains", "Yonkers", "New Rochelle", "Mount Vernon", "Scarsdale"],
      "Wyoming": ["Warsaw", "Perry", "Attica", "Arcade", "Castile", "Silver Springs"],
      "Yates": ["Penn Yan", "Dresden", "Dundee", "Rushville", "Himrod", "Branchport"]
    };
    
    countyCityMap.value = fallbackData;
    nyCounties.value = Object.keys(fallbackData).sort();
    
    try {
      const response = await fetch('/api/ny-locations');
      if (response.ok) {
        const data = await response.json();
        if (data.counties && Object.keys(data.counties).length > 0) {
          countyCityMap.value = data.counties;
          nyCounties.value = Object.keys(data.counties).sort();
        }
      }
    } catch (error) {
      console.log('Using fallback NY location data');
    }
  };

  const counties = computed(() => {
    // Remove " County" from display names
    return nyCounties.value.map(county => county.replace(/\s+County$/, ''));
  });
  
  const getCitiesForCounty = (county) => {
    // Handle both with and without "County" suffix for backward compatibility
    const lookupKey = county.includes(' County') ? county.replace(' County', '') : county;
    return countyCityMap.value[lookupKey] || [];
  };

  return {
    counties,
    getCitiesForCounty,
    loadNYLocationData
  };
}