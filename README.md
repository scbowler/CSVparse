# CSV File Parser

## Overview
> This application is used to parse a CSV file that is downloaded from a Google Sheet. The spreadsheet holds a record of LearningFuze students progress through the course. The data that is tracked is a students score on code snippets (or Prototypes) and projects. The initial version used a spreadsheet that only contained a single cohort at a time. In the newer version the spreadsheet contains data from multiple cohorts. The spreadsheet is populated from a Google Form that is completed by the reviewer of the students work. All options on the front end including Cohort Roster options and Student Lists based on selected roster are dynamically created from the CSV file via an AJAX call when a CSV file is selected.

## How to Use
>- Clone / Fork repo
- At this time the most up to date branch is **multi-class**, so make sure to switch to it
- Navigate to the current **Student Tracker Response Sheet**
- Download sheet as CSV file
- Open CSV index.php file in browser (With MAMP or equivalent)
- Chose the CSV file you downloaded
- Select A cohort roster before selecting anything else (This will be improved in the future)
- Choose option for needed output

   
## CSV File Structure
>- **Timestamp**: A time stamp from when the form was submitted
- **Date Reviewed**: The actual date the review was completed
- **LFZ Reviewer**: The name of the person completing the review
- **Class Roster**: Which class the student belongs too
- **Student Name**: The name of the student's whos work is being reviewed
- **Tracking Category**: The type of work being reviewed (*prototype or project*)
- **Tracking Item**: The title of the work being reviewed along with a due date
- **Score**: The numerical value the student received for his/her work
- **On time** If the work was on time (*yes or no*)

## Types of Output
>- **Prototypes Overview**: An overview of all the students from a selected cohort prototype progress
- **Personal Reports**: A detailed personal report of what a student has completed and what is missing (*Only for prototypes*)
- **Reviewer Counts**: A printout of how many reviews have been completed by each reviewer within a given date range (*originally used for payroll purposes, but no longer used*)
- **Error Checking**: A printout of any errors within the CSV file, mainly checking for duplicate entries

## Currently In-Progress Changes
> Being able to handle a spreadsheet that contains multiple cohorts. This is currently working, just not yet complete.

## Planned Future Updates
>- Directly pull CSV file from web
- Improved overall look including output
- Improved error handling
- Export output data to Google Sheet
- Overall code cleanup