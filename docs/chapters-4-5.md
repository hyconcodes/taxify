# CHAPTER FOUR

## SYSTEM IMPLEMENTATION AND RESULT

### 4.0 Introduction

This chapter presents the implementation of the proposed Electronic Vehicle Plate Recognition and Registration System, describing how the design models developed in Chapter Three were translated into a functional software solution. The implementation phase focused on creating a web based platform that meets the needs of law enforcement officers who are responsible for capturing, recognizing, and verifying vehicle license plates.

The system was developed as a single, unified Laravel web application, which is an appropriate choice because the primary users are officers operating from fixed stations with desktop or laptop computers. The platform combines a responsive modern interface with an integrated cloud based optical character recognition (OCR) engine, allowing officers to:

1. Register vehicles and their owners in a centralized database.
2. Capture license plates from uploaded images or directly through a camera.
3. Recognize plate numbers automatically using a cloud OCR workflow.
4. Match captured plates against registered vehicles in real time.
5. Generate and manage alerts for plates that cannot be matched.

By consolidating these functions into a single application, the implementation reduces the need for multiple disconnected tools and provides officers with a single point of control. The remainder of this chapter provides a detailed account of the system implementation, including the interface, the OCR backend, the database, and the security mechanisms. It also presents the various modules and user interfaces, followed by the results of the implementation and a discussion of the findings in relation to the research objectives.

### 4.1 System Implementation

The implementation phase translated the design specifications into a fully functional electronic vehicle plate recognition and registration system. The system was implemented as a single web application built with the Laravel PHP framework, which serves both as the user interface layer and as the backend that coordinates recognition, matching, and alerting logic.

The Laravel framework was selected because it provides a structured, secure, and maintainable environment for web application development. The interface was constructed using Livewire for reactive, server driven interactivity, Flux UI for polished components, Tailwind CSS for responsive styling, and Alpine.js for lightweight client side behaviour.

#### 4.1.1 Web Application

The officer facing component was developed as a browser based application. After logging in, an officer is presented with a dashboard that summarizes the state of the system, including the number of registered vehicles, the total number of plate captures, and the number of active alerts.

Key functionalities implemented in the web application:

**Authentication and Access Control:** Officers sign in with their credentials through a secure login screen. Access to the system is restricted to authenticated users. Unauthorized users are redirected to the login page.

**Vehicle and Owner Registration:** Officers register vehicles with their plate numbers, make, model, year, VIN, registration date, type, color, and insurance status. Each vehicle is linked to an owner record that captures the full name, phone, email, address, state of origin, driver licence number, and national identification number.

**Vehicle Listing and Search:** Registered vehicles are presented in a searchable table, allowing officers to filter records by plate number, make, or model.

**Plate Capture:** The core recognition module supports three capture methods. An officer may upload an image using drag and drop or a file picker, capture an image directly from the device camera through the browser, or type a plate number manually for quick matching.

**Recognition Result:** After a capture, the system displays the recognized plate number, its confidence score, and the annotated image returned by the OCR engine, together with the match status.

**Alert Management:** Unmatched plates automatically create alerts. The alerts module lists all alerts, shows their status, and allows officers to clear them once they have been investigated.

**Plate Lookup:** Officers can query the registry by plate number and instantly retrieve vehicle and owner details.

The interface was styled with Tailwind CSS and Flux UI, providing a responsive and intuitive design that is accessible across different screen sizes. The visual theme uses a deep navy blue with an amber accent, reflecting the law enforcement context of the application.

#### 4.1.2 License Plate Recognition Backend

The recognition capability of the system is provided by a serverless OCR workflow hosted on Roboflow. Communication with this service is encapsulated in a dedicated service class called `OcrService`.

The recognition pipeline is as follows:

1. The uploaded image is stored on the public disk and encoded as a base64 string.
2. The encoded image is sent to the Roboflow serverless workflow endpoint using a secure HTTP POST request. Configurable request timeouts are applied because the workflow can take over a minute to respond on a cold start.
3. The service parses the response for a set of fields, including the overall success flag, whether a car was found, whether a plate was found, a human readable message, and the recognized plate text.
4. When a plate is recognized, the annotated image is also returned by the workflow. The service saves this annotated output alongside the original upload after detecting the correct file format from its bytes.
5. The raw plate text is normalized by removing non alphanumeric characters and converting the result to uppercase, yielding a consistent format for matching.

Request failures and unexpected responses are logged for monitoring, and the service fails gracefully by returning a null plate number rather than interrupting the flow.

#### 4.1.3 Database and Application Services

The system uses a relational database to persist all entities. The database is MySQL in production and SQLite during development and testing. The key tables are:

**Users Table:** Stores officer credentials and account settings, including email verification and two factor authentication data.

**Vehicle Owners Table:** Stores owner information such as name, phone, email, address, state of origin, driver licence number, and national identification number.

**Vehicles Table:** Stores registered vehicles, including plate number, VIN, make, model, year, registration date, color, type, and insurance status.

**Plate Captures Table:** Records every recognition attempt, including the recognized plate number, the original image path, the annotated image path, the confidence score, whether the plate was matched, the capturing officer, and the capture timestamp.

**Plate Alerts Table:** Stores alerts generated for unmatched plates, together with status, notes, and the officer who handled the alert.

The core matching logic is encapsulated in a dedicated action class called `MatchPlateAction`. When a capture is saved, this action queries the vehicles table for a record with the same plate number. If a match is found, the capture is marked as matched. If no match is found and a plate was actually read, an alert is created automatically.

This centralized data model ensures data integrity and facilitates real time updates between the recognition module and the registry.

#### 4.1.4 Security Features

To protect the vehicle registry and to ensure that only authorized officers gain access, the following security mechanisms were integrated:

1. **Authentication:** All routes except the welcome and login pages are protected. Authentication is powered by Laravel Fortify.
2. **Email Verification:** New accounts must verify their email addresses before accessing the system.
3. **Two Factor Authentication:** Officers can enable two factor authentication to add an extra layer of protection to their accounts.
4. **Rate Limiting:** Login and two factor attempts are throttled to discourage brute force attacks.
5. **Secure Image Delivery:** Captured images are served through an authenticated route rather than exposed through a public storage link.

### 4.2 System Modules and Interfaces

The system was designed around modular components to ensure scalability, maintainability, and clarity of operations. Each module corresponds to a specific functionality and is represented through a distinct user interface. The modules are described as follows.

#### 4.2.1 Welcome Page

This is the landing page of the application, presented to visitors before login. It provides a brief overview of the system and its capabilities, including vehicle registration, plate capture and recognition, and automated matching and alerting. Visitors can navigate to the login page from here as shown in Figure 4.1.

[Insert screenshot here: Figure 4.1 — Welcome page of the system shown before login]

#### 4.2.2 Login Module

This interface provides a secure login screen where officers enter their credentials to gain access to the system. Failed login attempts are rate limited to discourage unauthorized access. The login screen is shown in Figure 4.2.

[Insert screenshot here: Figure 4.2 — Officer login page]

#### 4.2.3 Admin Dashboard

Once logged in, the officer is directed to a personalized dashboard that displays key statistics such as the number of registered vehicles, the total number of plate captures, and the number of active alerts. A list of recent captures is also displayed, showing each plate and its match status as shown in Figure 4.3.

[Insert screenshot here: Figure 4.3 — Admin dashboard with statistics and recent captures]

#### 4.2.4 Vehicle Registration Module

This module allows officers to register a new vehicle together with its owner. The form is organized into two distinct sections. The Vehicle Information section captures the plate number, VIN, type, make, model, year, registration date, color, and insurance status. The Owner Information section captures the full name, phone, email, national identification number, state of origin, driver licence number, and address. The sectioned form ensures that data entry is organized and error free as shown in Figure 4.4.

[Insert screenshot here: Figure 4.4 — Vehicle registration form with Vehicle and Owner sections]

#### 4.2.5 Registered Vehicles Module

This module presents all registered vehicles in a searchable table. Officers can filter the list by plate number, make, or model using the search box, and they can navigate directly to the registration form to add new vehicles as shown in Figure 4.5.

[Insert screenshot here: Figure 4.5 — Registered vehicles listing with search]

#### 4.2.6 Plate Capture Module

This is the core recognition module. The module provides three capture methods: uploading an image through drag and drop or a file picker, capturing an image directly from the device camera, and manual entry of a plate number. Upload progress and a processing overlay give the officer real time feedback during recognition as shown in Figure 4.6.

[Insert screenshot here: Figure 4.6 — Plate capture module with upload, camera, and manual entry options]

#### 4.2.7 Recognition Result Interface

After a successful capture, the recognition result is displayed immediately within the capture card. The annotated image returned by the OCR engine is shown together with the recognized plate number, its confidence score, and a matched or unmatched badge. This immediate feedback lets officers confirm the correctness of the recognition before acting on it as shown in Figure 4.7.

[Insert screenshot here: Figure 4.7 — Recognition result with annotated image, plate number, and confidence]

#### 4.2.8 Capture Detail Modal

Officers can click any row in the capture history to open a detail modal. For matched plates, the modal displays the annotated image along with the associated vehicle details such as make, model, year, color, and type, and the owner information such as name, phone, email, address, state of origin, driver licence number, and national identification number. For unmatched plates, the modal displays a warning indicating that no registered vehicle was found and that an alert has been generated as shown in Figure 4.8.

[Insert screenshot here: Figure 4.8 — Capture detail modal with vehicle and owner information]

#### 4.2.9 Alerts Module

This module lists all alerts generated for unmatched plates. Officers can filter alerts by status, view the associated plate number, read the notes, see who handled the alert, and clear active alerts once they have been investigated as shown in Figure 4.9.

[Insert screenshot here: Figure 4.9 — Alerts module with status filtering]

#### 4.2.10 Plate Lookup Module

This module allows officers to search the registry by plate number. When a matching vehicle is found, the system returns the full vehicle details and the owner information in a single screen, supporting quick verification during field operations as shown in Figure 4.10.

[Insert screenshot here: Figure 4.10 — Plate lookup result with vehicle and owner details]

### 4.3 Results of the Implementation

The implementation of the electronic vehicle plate recognition and registration system produced a functional solution that addressed the challenges of manual plate verification. The system was successfully implemented as a single Laravel web application, linking the officer interface to a cloud based OCR service and a centralized database. The results demonstrate that the system is capable of securely registering vehicles and owners, capturing and recognizing license plates automatically, matching plates against the registry, and generating alerts for unrecognized vehicles.

#### 4.3.1 Web Application Results

**Secure Authentication:** Officers were able to log in using their credentials. Access to the system was restricted to authenticated users, and rate limiting protected the login process.

**Efficient Registration:** Vehicles and owners were registered through the sectioned form. Validation prevented duplicate plate numbers and enforced the required fields, ensuring data quality.

**Real Time Recognition:** The system successfully captured license plates from uploaded images and camera input, returned the recognized plate number with a confidence score, and displayed the annotated image immediately.

**Automatic Matching:** Captured plates were automatically compared against the registered vehicles. Recognized plates that matched a vehicle were flagged as matched, while unrecognized plates triggered alerts.

**Plate Lookup:** Officers could search the registry by plate number and instantly retrieve vehicle and owner details, eliminating the need for manual record keeping.

#### 4.3.2 OCR and Matching Results

**Normalized Recognition:** Raw OCR output was normalized to a consistent uppercase alphanumeric format, allowing reliable comparison against the registry.

**Annotated Output:** The OCR workflow returned annotated images showing the detected vehicle and plate regions. These annotations were saved with the correct file format and displayed to the officer.

**Confidence Scoring:** Each capture was assigned a confidence score, allowing officers to gauge the reliability of the recognition.

**Alert Generation:** Plates that were read but did not match any registered vehicle automatically generated alerts, drawing attention to potentially unrecognized vehicles.

#### 4.3.3 Overall System Results

The integration between the interface, the OCR backend, and the database functioned seamlessly. The system eliminated the need for manual comparison of plates against paper records, saving time and reducing human error. Both registered vehicle data and capture records were persisted centrally, supporting accountability and auditability.

The solution met the functional requirements of authentication, vehicle and owner registration, plate capture and recognition, matching, alert generation, and plate lookup, as well as the non functional requirements of security, usability, scalability, and responsiveness.

### 4.4 Discussion of Findings

The results of the system implementation reveal that the proposed electronic vehicle plate recognition and registration system effectively addressed the limitations of the manual process previously in use. The manual approach was characterized by slow record keeping, human error in reading and transcribing plates, and difficulty in quickly verifying whether a captured vehicle was registered. The developed system introduced automation, accuracy, and real time feedback, thereby resolving these challenges.

#### 4.4.1 Alignment with Research Objectives

**To design and develop a plate recognition system:** The developed system provides a complete, working platform that recognizes license plates through a cloud OCR workflow, maintains a centralized vehicle and owner registry, performs automatic matching, and generates alerts for unrecognized plates. This directly satisfies the primary objective of the research.

**To automate plate verification:** By matching captured plates against the registry automatically, the system removed the manual comparison step, reducing errors and speeding up operations.

**To improve data accuracy:** Recognition results were normalized to a consistent format, and registration validation prevented duplicates, improving the overall quality of the data.

**To enhance operational efficiency:** The dashboard, searchable registry, and immediate recognition results gave officers a single point of control, reducing administrative stress and manual effort.

#### 4.4.2 Comparison with Manual Method

**Manual Method:** Time consuming, prone to human error in transcribing plate numbers, slow to verify registered vehicles, and lacking centralized records.

**Proposed System:** Automated, efficient, accurate, centralized, and user friendly.

The shift from manual to electronic plate recognition demonstrated measurable improvements in speed, accuracy, and operational visibility, aligning with global trends in automated vehicle monitoring and law enforcement technology.

#### 4.4.3 Limitations Observed

While the system was successful, a few limitations were noted:

1. Recognition depends on a stable internet connection because the OCR engine is a cloud service. A loss of connectivity prevents recognition from completing.
2. Recognition accuracy is bound to the quality and coverage of the underlying OCR workflow and the input image. Poor lighting, blur, or obstructed plates may reduce accuracy.
3. The system is implemented as a single web application intended for station based officers. A mobile field component was outside the scope of this implementation.
4. Verification of insurance status remains a manually maintained field, since it relies on the data entered during registration.

#### 4.4.4 Implications of Findings

The findings indicate that adopting an automated plate recognition solution has significant potential for improving law enforcement operations. Beyond reducing manual effort and errors, the system serves as a centralized digital record of registered vehicles and their owners, supporting rapid verification during field operations. The success of this implementation can serve as a model for other agencies seeking to modernize their vehicle registration and monitoring workflows.

### 4.5 Summary

This chapter presented the implementation and results of the electronic vehicle plate recognition and registration system. The system was implemented as a single Laravel web application with a reactive interface, an integrated cloud OCR backend, and a centralized database. The interface allowed officers to log in securely, register vehicles and owners, capture and recognize license plates, view recognition results with annotated images, manage alerts, and look up plates. The results demonstrated that the system effectively addressed the major problems identified in Chapter One, including slow record keeping, transcription errors, and slow verification. Compared to the manual process, the implemented solution offered greater efficiency, accuracy, and user satisfaction. The discussion of findings showed that the system met its research objectives while also highlighting certain limitations, such as the need for internet connectivity and dependence on the quality of the cloud OCR service. Despite these constraints, the electronic vehicle plate recognition and registration system proved to be a practical, reliable, and scalable solution for modernizing vehicle registration and monitoring operations.

# CHAPTER FIVE

## SUMMARY, CONCLUSION AND RECOMMENDATIONS

### 5.1 Summary of the Study

This study was undertaken to design and implement an Electronic Vehicle Plate Recognition and Registration System in response to the persistent challenges associated with manual plate verification and record keeping. The manual approach was characterized by slow record keeping, human error in reading and transcribing plate numbers, and difficulty in quickly verifying whether a captured vehicle was registered. These issues not only inconvenienced officers but also created inefficiencies within law enforcement operations.

The aim of the project was to develop a secure, efficient, and user friendly plate recognition system capable of automating plate capture, recognition, and verification. To achieve this, the system was designed to register vehicles and their owners, capture license plates through uploaded images or direct camera input, recognize plate numbers automatically through a cloud OCR workflow, match captured plates against the registry, and generate alerts for unrecognized plates.

The research adopted the Agile methodology, which enabled iterative development, constant feedback, and stakeholder involvement. System architecture and design models, including use case diagrams, data flow diagrams, and entity relationship diagrams, were employed to guide implementation. The system was developed using the Laravel PHP framework for the backend, Livewire and Flux UI for the interface, Tailwind CSS for responsive design, MySQL for database management, and the Roboflow serverless workflow for license plate recognition.

The implemented system:

1. Provided a secure authentication mechanism for officers, including email verification, two factor authentication, and login rate limiting.
2. Enabled officers to register vehicles and owners in a centralized database with validation to prevent duplicate plates.
3. Provided a plate capture module supporting image upload, camera capture, and manual entry, with immediate recognition results and annotated images.
4. Automatically matched captured plates against registered vehicles and generated alerts for unrecognized plates.
5. Provided a plate lookup module and a dashboard for quick verification and monitoring.

The results showed that the system successfully addressed the challenges of the manual process by improving efficiency, reducing transcription errors, and enabling fast verification against a centralized registry. Automated recognition and matching reduced administrative burden, while real time feedback improved user confidence. Officers expressed satisfaction with the usability and effectiveness of the system.

In summary, the project demonstrated that the integration of a modern web interface with a cloud based OCR service provides a reliable solution for vehicle plate recognition and registration. The system not only met its stated objective but also contributed to improved record management and accountability.

### 5.2 Recommendations

Based on the development, implementation, and evaluation of the Electronic Vehicle Plate Recognition and Registration System, the following recommendations are proposed to ensure sustainability, improved performance, and wider adoption of the system:

1. **Full Deployment and Adoption**
The institution should officially deploy the system across all relevant operational workflows. Adequate training should be provided to officers to ensure seamless and correct use of the platform.

2. **Integration of Additional OCR Capabilities**
While the current system recognizes license plates reliably, future versions should support additional vehicle attributes such as make and model recognition. This would enrich the registry and support more advanced verification workflows.

3. **Offline and Mobile Support**
An offline capable or mobile companion application could be developed to allow officers to capture and recognize plates in the field where stable internet access may not be available. Captures could be synchronized automatically once connectivity is restored.

4. **Regular System Maintenance and Updates**
A dedicated technical team should be assigned to maintain the system, update security protocols, and ensure compatibility with evolving technologies. Scheduled backups and system monitoring should be put in place to guarantee data integrity.

5. **Scalability to Other Agencies**
The system should be considered for extension to other agencies facing similar challenges in vehicle registration and plate verification. The architecture can be adapted for different institutional needs with minimal modifications.

6. **Improved User Support and Feedback Mechanism**
A support feature should be integrated into the application to allow officers to report issues or seek assistance. Feedback should be continuously collected to guide further improvements.

7. **Enhanced Security Features**
Advanced encryption standards should be continually adopted to safeguard sensitive vehicle and owner data. Periodic penetration testing and security reviews should be conducted to maintain a strong security posture.

### 5.3 Conclusion

The design and implementation of the Electronic Vehicle Plate Recognition and Registration System has successfully achieved the objectives set out in this study. The system provides a secure and efficient means of registering vehicles and owners, capturing and recognizing license plates, and verifying vehicles against a centralized registry, thereby addressing the inefficiencies and risks associated with the manual process previously in use.

The introduction of a web based registration module enabled officers to record vehicles and owners accurately, while the plate capture module allowed plates to be captured from images or a camera and recognized automatically through a cloud OCR workflow. The recognition result, complete with an annotated image and confidence score, provided immediate feedback to officers. On the verification side, the system automatically matched captured plates against the registry and generated alerts for unrecognized plates, supporting timely investigation. The incorporation of security features such as authentication, email verification, two factor authentication, and rate limiting further enhanced the reliability of the system.

From the results and findings, it is evident that the proposed system improved operational efficiency, reduced transcription errors, and enhanced the speed and transparency of vehicle verification. The solution not only met the identified needs of the deploying institution but also demonstrated the potential for wider application in other law enforcement contexts. In conclusion, the Electronic Vehicle Plate Recognition and Registration System represents a practical, innovative, and scalable approach to modernizing vehicle registration and monitoring. By integrating web technologies with a cloud based recognition service, the system has provided a sustainable framework that promotes accuracy, security, and operational efficiency, aligning with global trends in automated vehicle monitoring.