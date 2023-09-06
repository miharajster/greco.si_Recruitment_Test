Thank you for taking the time to work on this test. We offer no guarantees, but we feel this could move us closer to understanding whether we are a good match.

The test consists of two parts:

- The assignment (coding test)
- Technical questions
Deliverables:

- CT-Assets-Backend-{name surname}.ZIP (code and database export)
- CT-Answers.txt (you can use markdown)
## 1. The Assignment (Coding Test)
### Test Datasets
You will be working with three public datasets:

- List of UNESCO World Heritage Sites (XML)
- A list of first names (TXT)
- A list of last names (TXT)
If you're having trouble downloading these files, they are all available in this Git repository and at their sources:

UNESCO World Heritage sites http://whc.unesco.org/en/list/xml/

First and last names https://github.com/smashew/NameDatabases

### Database
1. Import the list of UNESCO sites (at minimum site name ("site"), latitude and longitude).

2. Create a list of randomly generated personal names ("Name Surname" pairs). They should be as diverse as possible (e.g. don't let all names start with "A") and the names should not repeat. These will be our travel agents.

3. Assign each of the UNESCO sites a unique travel agent.

### Assignment
Create a website where the user can type in a location (e.g. Betnavska cesta 120, Maribor, Slovenia).

The website should then display a list of the 5 closest (by air) UNESCO sites with their corresponding tourist agent.

Hint 1: you can use the Google Maps API to get the coordinates or the typed-in location, but not to query distances

Hint 2: typing in just the coordinates (not the location) is also acceptable (but not preferred)

### Technology
Feel free to use whatever languages, web technologies and databases you feel comfortable with. But please keep in mind that we are testing multiple candidates and cannot spend much time setting up environments. If your code is something other than Node.js or PHP and MySQL or PostgreSQL, please provide a link where we can test your solution or supply a Docker image.

# 2. Technical Questions
- How much time did you spend working on your solution?
- What could you do to improve it if you had more time?
- How could you make your code run faster? What if there were a million records in the UNESCO database? Have you ever had to deal with such issues?
- What is the most useful new feature of your chosen programming language? Did you get a chance to use it in this task? If not, what would be a good use case for it?
- Please describe yourself using JSON.
- Please send your deliverables to the person you were in touch with (recruiter, project manager, engineer, CTO).

We'll get back to you as soon as possible!
___
This page was directly inspired by https://github.com/justeat/JustEat.RecruitmentTest.
