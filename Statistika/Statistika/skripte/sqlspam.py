import random

# Define technologies and their corresponding problems
technologies = {
    "ADSL": ["Nema link", "Nema internet", "Lose radi internet", "Wifi problemi", "Ostalo"],
    "WiFi": ["Offline Antena", "Wifi problemi", "Povezivanje ruter", "Los i spor internet", "Ostalo"],
    "GLight": ["Offline ruter", "Offline SW", "spora konekcija", "wifi lokal", "Ostalo"],
    "GPON": ["LOSLOBI", "Dying gasp", "LAOMI", "Nema zivota ONT", "Wifi lokal", "Ostalo"],
    "WiFi6E": ["Spor internet", "Wifi lokal", "Offline ruter", "Offline antena", "Ostalo"],
    "IPTV": ["Nema signala", "STB ne radi", "Daljinski", "Problemi sa slikom", "Aplikacija", "Ostalo"],
    "Prebacen": ["KS", "TS", "Hosting", "BIZ", "Ostalo"]
}

# Define users and the corresponding technologies they will use
users_and_technologies = {
    "test_adsl": "ADSL",
    "WiFi_test": "WiFi",
    "GLight_test": "GLight",
    "GPON_test": "GPON",
    "WiFi6E_test": "WiFi6E",
    "IPTV_test": "IPTV",
    "Prebacen_test": "Prebacen"
}

# Generate SQL insert statements
sql_statements = []
for _ in range(500):
    username = random.choice(list(users_and_technologies.keys()))
    technology = users_and_technologies[username]
    problem = random.choice(technologies[technology])
    sql_statements.append(f"INSERT INTO user_entries (username, technology, problem) VALUES ('{username}', '{technology}', '{problem}');")

# Print out all SQL statements
for statement in sql_statements:
    print(statement)
