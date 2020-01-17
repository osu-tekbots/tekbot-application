<?php
namespace Email;

use Util\Security;
use DataAccess\QueryUtils;

class EquipmentRentalMailer extends Mailer {
    /**
     * Constructs a new instance of a mailer specifically for capstone project-related emails
     *
     * @param string $from the from address for emails
     * @param string|null $subjectTag an optional subject tag to prefix the provided subject tag with
     */
    public function __construct($from, $subjectTag = null) {
        parent::__construct($from, $subjectTag);
    }


    public function sendPaidEquipmentFeesEmail($user, $equipmentFee) {
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $subject = "Successfully Submitted Equipment Fee Request";

        $touchnetID = $equipmentFee->getPaymentInfo();
        $feeAmount = $equipmentFee->getAmount();

        $message = "
        Dear $userName,

        You have sucessfully submitted a fee payment request with touchnet ID: $touchnetID, with an amount of
        $feeAmount. 
        
        A Tekbot employee will verify that the payment has been able to process 
        and will send you an update email when it is confirmed. Thank you!

        Sincerely,

        TekBots 
        Oregon State University
        KEC 1110 (Hours: 10AM - 1PM)
        ";

        return $this->sendEmail($user->getEmail(), $subject, $message);
    }

    public function sendApproveEquipmentFeesEmail($user, $equipmentFee) {
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $subject = "Fee Payment Approved (Equipment Checkout)";

        //$feeID = $equipmentFee->getFeeID();
        $touchnetID = $equipmentFee->getPaymentInfo();
        $feeAmount = $equipmentFee->getAmount();

        $message = "
        Dear $userName,

        Your payment of amount $$feeAmount and touchnet ID: $touchnetID has been approved.  

        Thank you for paying your fees and we hope to see you again soon.

        Sincerely,

        TekBots 
        Oregon State University
        KEC 1110 (Hours: 10AM - 1PM)
        ";

        return $this->sendEmail($user->getEmail(), $subject, $message);
    }

    public function sendRejectEquipmentFeesEmail($user, $equipmentFee) {
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $subject = "Fee Payment Denied (Equipment Checkout)";

        //$feeID = $equipmentFee->getFeeID();
        $touchnetID = $equipmentFee->getPaymentInfo();
        $feeAmount = $equipmentFee->getAmount();

        $message = "
        Dear $userName,

        Your payment of amount $$feeAmount and touchnet ID: $touchnetID has been rejected.  
        Please check the fee notes carefully and try to submit payment again.
        
        If you are still having trouble, feel free to come in during our store hours KEC 1110 (10 AM - 1 PM) and 
        an employee will be able to help you out.

        Thank you for paying your fees and we hope to see you again soon.

        Sincerely,

        TekBots 
        Oregon State University
        KEC 1110 (Hours: 10AM - 1PM)
        ";

        return $this->sendEmail($user->getEmail(), $subject, $message);
    }



    public function sendAssignEquipmentFeesEmail($user, $equipmentFee, $link) {
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $subject = "Assigned Equipment Fee";

        $feeID = $equipmentFee->getFeeID();
        $feeAmount = $equipmentFee->getAmount();
        $feeNotes = $equipmentFee->getNotes();

        $message = "
        Dear $userName,

        You have been assigned a fee regarding an equipment checkout.  
        The fee is for the amount of $$feeAmount. 
        Fee Notes: $feeNotes
        
        Please pay this amount by clicking $link.
        After going to 'My Profile', hit the 'My Fees' tab, and then follow the instructions after hitting the 'Pay' button.
        
        A Tekbot employee will verify that the payment has been able to process 
        and will send you an update email when it is confirmed. Thank you!

        Sincerely,

        TekBots 
        Oregon State University
        KEC 1110 (Hours: 10AM - 1PM)
        ";

        return $this->sendEmail($user->getEmail(), $subject, $message);
    }

 /**
     * Sends a confirmation email to the user after reserving their equipment
     *
     * @param \Model\EquipmentReservation $project 
     * @param string $link the URL allowing the user to view the project
     * @return boolean true on success, false otherwise
     */
    public function sendReservationAgreementEmail($user, $link) {
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $subject = "Equipment Reserved For 1 Hour";

        $message = "
        Dear $userName,

        Thank you for your equipment reservation!

        This reservation is just an indication that you would like to checkout the item and will pick it up within the next hour.
        
        As long as you come pick up the item within the next hour, you will be able to rent out the equipment for a full 24 hours from pickup.  
        
        If you do not pick it up within the next hour, the reservation will cancel and the equipment will be available for others to reserve.
            
        STEPS TO COMPLETE:
        1. Come to TekBots within the next hour to pick up and checkout the equipment you reserved.
        

        If you no longer need the equipment or accidently created a reservation, cancel it here: $link


        Sincerely,

        TekBots 
        Oregon State University
        KEC 1110 (Hours: 10AM - 1PM)
        ";

        return $this->sendEmail($user->getEmail(), $subject, $message);
    }

    /**
     * Sends an email to the user of a checkout informing them about their checkout.
     *
     * @param \Model\CapstoneProject $project the project that is being approved
     * @param string $link the URL at which the project can be viewed
     * @return boolean true on success, false otherwise
     */
    public function sendEquipmentCheckoutEmail($checkout, $user, $link) {
        $dt = $checkout->getDeadlineTime();
        $timestamp = strtotime($dt);
        //$deadlineTime = date(QueryUtils::DATE_STR, $timestamp);
        //$tempDate = DateTime::createFromFormat('j-M-Y', $checkout->getDeadlineTime());
        //$deadlineTime = $tempDate->format(QueryUtils::DATE_STR);
        $contractID = $checkout->getContractID();
        //$pickupTime = $checkout->getPickupTime();
        $checkoutID = $checkout->getCheckoutID();
        $equipmentID = $checkout->getEquipmentID();
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $subject = "Equipment Checkout: $equipmentID";

        $message = "
    Dear $userName,

    You have checked out an equipment!
    ---------------------------
    Equipment ID: $equipmentID
    Contract Duration: $contractID day(s)
    ---------------------------

    To view the exact date and time your checkout out ends, view it at this link: $link

    You must return the item before the contract ends!  Failure to return the item before the deadline time can result in late fees.

    Below are the rental agreements you agreed to when reserving the equipment.
    
    Terms & Conditions:

        Responsibility and Use & Disclaimer Warrenties: You are responsible for the use of the rented
        items. You assume all risks inherent to the operation and use of rented items, and agree to
        assume the entire responsibility for the defense of, and to pay, indemnity and hold Above All
        Party Rentals harmless from and hereby release Above All Party Rentals from, all claims for
        damage to property or bodily injury (including death) resulting from the use, operation or
        possession of the items, whether or not it be claimed or found that such damage or injury
        resulted in whole or part from Above All Party Rentals negligence, from the defective condition
        of the items, or any other cause. YOU AGREE THAT NO WARRANTIES EXPRESSED OR IMPLIED,
        INCLUDING MERCHANTIBILITY OR FITNESS FOR A PARTICULAR PURPOSE HAVE BEEN MADE IN
        CONNECTION WITH THE EQUIPMENT RENTED.

        Equipment Failure: You agree to immediately discontinue the use of rented items should it at
        any time become unsafe or in a state of disrepair, and will immediately (one hour or less) notify
        Above All Party Rentals of the facts. Above All Party Rentals agrees at our discretion to make the
        items operable in a reasonable time, or provide a like items if available, or make a like item
        available at another time, or adjust rental charges, The provision does not relieve renter from
        obligations of contract. In all events Above All Party Rentals shall not be responsible for injury or
        damage resulting in failure or defect of rented item.
    
        Equipment Responsibility: Renter is responsible for equipment from time of possession to time
        of return. Renter assumes the entire risk of loss, regardless of cause. If items are lost, stolen,
        damaged, renter will assume cost of replacemt or repair, including labor costs. Renter shall pay
        a reasonable cleaning charge for rented items returned dirty.

        Time of Return: Renter's right of possession terminates upon the expiration of rental period set
        forth on the contract. Time is of the essence in this contract. Any extension must be agreed
        upon in writing.

        Late Returns: Renter shall return rented items to Above All Party Rentals during regular
        business hours, promptly upon. or prior to expiration of rental period. If renter does not timely
        return, the rental rate shall continue until items are returned.

    Sincerely,

    TekBots 
    Oregon State University
    KEC 1110 (Hours: 10AM - 1PM)
        ";

        return $this->sendEmail($user->getEmail(), $subject, $message);
    }


    /**
     * Sends a confirmation email to the proposer after the have submitted their project.
     *
     * @param \Model\CapstoneProject $project 
     * @param string $link the URL allowing the user to view the project
     * @return boolean true on success, false otherwise
     */
    public function sendProjectSubmissionConfirmationEmail($project, $link) {
        $userName = $project->getProposer()->getFirstName() . ' ' . $project->getProposer()->getLastName();
        $pid = $project->getId();
        $title = $project->getTitle();

        $subject = "Project Submitted for Approval";

        $NDA_message = $project->getNdaIp()->getId() == CapstoneProjectNDAIP::NO_AGREEMENT_REQUIRED ? '' : '
        If your project requires an NDA and/or IP agreement, it must be indicated at the time the students select the 
        projects.

        If your company intends to provide proprietary materials or confidential information requiring an NDA, OSU can 
        arrange for a written agreement to reviewed and signed amongst the students, your company, and OSU.

        Such an agreement will authorize the students to use and discuss the provided materials or information with 
        each other and their instructor in confidence.

        The university will not participate in any agreement that requires students to transfer intellectual property 
        rights ownership to your company or puts overly burdensome confidentiality obligations on the students.

        Though OSU certainly appreciates your company’s sponsorship, we strongly discourage any agreements that could 
        deter students from sharing the results of their academic work at OSU with fellow students, parents or future 
        employers.

        This does not prevent a separate arrangement between you each student individually.';

        $message = "
        Dear $userName,

        Thank you for submitting your project!
        ---------------------------
        Project ID: $pid
        Project Title: $title
        ---------------------------

        Your project is now awaiting for approval from an administrator.

        Your project can now be viewed at: $link

        $NDA_message

        * Your project has the ability to be modified by an administrator for final revisions *

        Sincerely,

        Senior Design Capstone Team
        Oregon State University
        ";

        return $this->sendEmail($project->getProposer()->getEmail(), $subject, $message);
    }

    /**
     * Sends an email to the proposer of a project informing them that their project was approved.
     *
     * @param \Model\CapstoneProject $project the project that is being approved
     * @param string $link the URL at which the project can be viewed
     * @return boolean true on success, false otherwise
     */
    public function sendProjectApprovedEmail($project, $link) {
        $userName = $project->getProposer()->getFirstName() . ' ' . $project->getProposer()->getLastName();
        $pid = $project->getId();
        $title = $project->getTitle();

        $subject = "Project Approved: $title";

        $content = "
        Dear $userName,

        Your project has been approved!
        ---------------------------
        Project ID: $pid
        Project Title: $title
        ---------------------------

        Your project can now be viewed at: $link

        * Your project has the ability to be modified by an administrator for final revisions *

        Sincerely,

        Senior Design Capstone Team
        Oregon State University
        ";

        return $this->sendEmail($project->getProposer()->getEmail(), $subject, $content);
    }

    /**
     * Sends an email to the proposer of a project informing them that their project was rejected.
     *
     * @param \Model\CapstoneProject $project the project that is being rejected
     * @param string $reason the reason the project is being rejected
     * @return boolean true on success, false otherwise
     */
    public function sendProjectRejectedEmail($project, $reason) {
        $userName = $project->getProposer()->getFirstName() . ' ' . $project->getProposer()->getLastName();
        $pid = $project->getId();
        $title = $project->getTitle();

        $subject = "Project Rejected: $title";

        $content = "
        Dear $userName,

        We regret to inform you that your project was not approved.
        ---------------------------
        Project ID: $pid
        Project Title: $title
        Reason for rejection: $reason
        ---------------------------

        If you have any further questions, please send us an email at heer@oregonstate.edu.

        Sincerely,

        Senior Design Capstone Team
        Oregon State University
        ";

        return $this->sendEmail($project->getProposer()->getEmail(), $subject, $content);
    }


    /**
     * Sends a notification email to the website admins about actions they need to take.
     *
     * @param int $pendingProjects the number of projects to approve
     * @param int $pendingCategories the number of projects that need categorization
     * @param string[] $addresses and array of addresses to send the email to
     * @return boolean
     */
    public function sendProjectNotificationsToAdmin($pendingProjects, $pendingCategories, $addresses) {

        $subject = "Projects Need To Be Approved!";

        $message = "
        Just a reminder, you have
        $pendingProjects - Pending Projects that need to be approved.
        $pendingCategories - Pending Projects that need categorization.
        ";

        return $this->sendEmail($addresses, $subject, $message);

    }

    
}
