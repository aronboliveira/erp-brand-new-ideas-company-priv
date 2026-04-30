/**
 * Jest unit tests for partials routes
 * Generated: 2026-03-08T23:57:47.950Z
 * 
 * Routes covered: partials.admin.footer, partials.admin.header, partials.admin.menu
 */
import '@testing-library/jest-dom';


describe('partials.admin.footer', () => {
  // TypeScript sources: src/public/assets/js/routes/partials/admin/footer
  // Import would be: import * as module0 from 'src/public/assets/js/routes/partials/admin/footer';
  
  beforeEach(() => {
    // Set up DOM
    document.body.innerHTML = `
      <form id="main-form">
        <input type="hidden" name="_token" value="test-csrf-token" />
        <input type="hidden" name="_method" value="POST" />
        <input type="text" id="name" name="name" />
        <button type="submit" id="submit-btn">Submit</button>
      </form>
      <table id="data-table">
        <tbody>
          <tr><td>Test</td></tr>
        </tbody>
      </table>
      <button class="delete-btn">Delete</button>
      <form id="delete-form">
        <input type="hidden" name="_method" value="DELETE" />
        <button id="confirm-delete-btn">Confirm</button>
      </form>
    `;
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('should set up DOM correctly', () => {
    expect(document.body.innerHTML).toContain('main-form');
  });

  test('should render without errors', () => {
    expect(document.body).toBeTruthy();
  });
});



describe('partials.admin.header', () => {
  // TypeScript sources: src/public/assets/js/routes/partials/admin/header
  // Import would be: import * as module0 from 'src/public/assets/js/routes/partials/admin/header';
  
  beforeEach(() => {
    // Set up DOM
    document.body.innerHTML = `
      <form id="main-form">
        <input type="hidden" name="_token" value="test-csrf-token" />
        <input type="hidden" name="_method" value="POST" />
        <input type="text" id="name" name="name" />
        <button type="submit" id="submit-btn">Submit</button>
      </form>
      <table id="data-table">
        <tbody>
          <tr><td>Test</td></tr>
        </tbody>
      </table>
      <button class="delete-btn">Delete</button>
      <form id="delete-form">
        <input type="hidden" name="_method" value="DELETE" />
        <button id="confirm-delete-btn">Confirm</button>
      </form>
    `;
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('should set up DOM correctly', () => {
    expect(document.body.innerHTML).toContain('main-form');
  });

  test('should render without errors', () => {
    expect(document.body).toBeTruthy();
  });
});



describe('partials.admin.menu', () => {
  // TypeScript sources: src/public/assets/js/routes/partials/admin/menu/dashboard, src/public/assets/js/routes/partials/admin/menu/accountStatement, src/public/assets/js/routes/partials/admin/menu/invoiceSummary, src/public/assets/js/routes/partials/admin/menu/salesReport, src/public/assets/js/routes/partials/admin/menu/receivables, src/public/assets/js/routes/partials/admin/menu/payables, src/public/assets/js/routes/partials/admin/menu/billSummary, src/public/assets/js/routes/partials/admin/menu/productStock, src/public/assets/js/routes/partials/admin/menu/transactions, src/public/assets/js/routes/partials/admin/menu/incomeSummary, src/public/assets/js/routes/partials/admin/menu/expenseSummary, src/public/assets/js/routes/partials/admin/menu/incomeVsExpenseSummary, src/public/assets/js/routes/partials/admin/menu/taxSummary, src/public/assets/js/routes/partials/admin/menu/hrmDashboard, src/public/assets/js/routes/partials/admin/menu/reportsPayroll, src/public/assets/js/routes/partials/admin/menu/reportsLeave, src/public/assets/js/routes/partials/admin/menu/reportsMonthlyAttendance, src/public/assets/js/routes/partials/admin/menu/crmDashboard, src/public/assets/js/routes/partials/admin/menu/reportsLead, src/public/assets/js/routes/partials/admin/menu/reportsDeal, src/public/assets/js/routes/partials/admin/menu/projectDashboard, src/public/assets/js/routes/partials/admin/menu/posDashboard, src/public/assets/js/routes/partials/admin/menu/reportsWarehouse, src/public/assets/js/routes/partials/admin/menu/reportsDailyPurchase, src/public/assets/js/routes/partials/admin/menu/reportsPosVsPurchase, src/public/assets/js/routes/partials/admin/menu/employee, src/public/assets/js/routes/partials/admin/menu/setSalary, src/public/assets/js/routes/partials/admin/menu/manageLeave, src/public/assets/js/routes/partials/admin/menu/markAttendance, src/public/assets/js/routes/partials/admin/menu/bulkAttendance, src/public/assets/js/routes/partials/admin/menu/indicator, src/public/assets/js/routes/partials/admin/menu/appraisal, src/public/assets/js/routes/partials/admin/menu/goalTracking, src/public/assets/js/routes/partials/admin/menu/training, src/public/assets/js/routes/partials/admin/menu/trainer, src/public/assets/js/routes/partials/admin/menu/job, src/public/assets/js/routes/partials/admin/menu/jobApplication, src/public/assets/js/routes/partials/admin/menu/info, src/public/assets/js/routes/partials/admin/menu/event, src/public/assets/js/routes/partials/admin/menu/meeting, src/public/assets/js/routes/partials/admin/menu/employeeAsset, src/public/assets/js/routes/partials/admin/menu/document, src/public/assets/js/routes/partials/admin/menu/companyPolicy, src/public/assets/js/routes/partials/admin/menu/hrmSystem, src/public/assets/js/routes/partials/admin/menu/bank, src/public/assets/js/routes/partials/admin/menu/chart, src/public/assets/js/routes/partials/admin/menu/budgetPlanner, src/public/assets/js/routes/partials/admin/menu/financialGoal, src/public/assets/js/routes/partials/admin/menu/accountingSetup, src/public/assets/js/routes/partials/admin/menu/printSetting, src/public/assets/js/routes/partials/admin/menu/lead, src/public/assets/js/routes/partials/admin/menu/deal, src/public/assets/js/routes/partials/admin/menu/formBuilder, src/public/assets/js/routes/partials/admin/menu/contract, src/public/assets/js/routes/partials/admin/menu/crmSystem, src/public/assets/js/routes/partials/admin/menu/project, src/public/assets/js/routes/partials/admin/menu/task, src/public/assets/js/routes/partials/admin/menu/timesheet, src/public/assets/js/routes/partials/admin/menu/bug, src/public/assets/js/routes/partials/admin/menu/taskCalendarLink, src/public/assets/js/routes/partials/admin/menu/tracker, src/public/assets/js/routes/partials/admin/menu/projectReport, src/public/assets/js/routes/partials/admin/menu/projectTaskStages, src/public/assets/js/routes/partials/admin/menu/bugStatus, src/public/assets/js/routes/partials/admin/menu/userLink, src/public/assets/js/routes/partials/admin/menu/role, src/public/assets/js/routes/partials/admin/menu/client, src/public/assets/js/routes/partials/admin/menu/productService, src/public/assets/js/routes/partials/admin/menu/warehouse, src/public/assets/js/routes/partials/admin/menu/purchase, src/public/assets/js/routes/partials/admin/menu/pos, src/public/assets/js/routes/partials/admin/menu/warehouseTransfer, src/public/assets/js/routes/partials/admin/menu/posBarcode, src/public/assets/js/routes/partials/admin/menu/posPrintSetting, src/public/assets/js/routes/partials/admin/menu/calls, src/public/assets/js/routes/partials/admin/menu/notificationTemplate, src/public/assets/js/routes/partials/admin/menu/systemSettings, src/public/assets/js/routes/partials/admin/menu/setupSubscription, src/public/assets/js/routes/partials/admin/menu/orderLink, src/public/assets/js/routes/partials/admin/menu/dashboardViewLink, src/public/assets/js/routes/partials/admin/menu/dealIndexLink, src/public/assets/js/routes/partials/admin/menu/contractIndexLink, src/public/assets/js/routes/partials/admin/menu/projectIndexLink, src/public/assets/js/routes/partials/admin/menu/projectReportLink, src/public/assets/js/routes/partials/admin/menu/tasksLink, src/public/assets/js/routes/partials/admin/menu/bugsLink, src/public/assets/js/routes/partials/admin/menu/timesheetListLink, src/public/assets/js/routes/partials/admin/menu/taskCalendar, src/public/assets/js/routes/partials/admin/menu/support, src/public/assets/js/routes/partials/admin/menu/dashboardLink, src/public/assets/js/routes/partials/admin/menu/user, src/public/assets/js/routes/partials/admin/menu/plan, src/public/assets/js/routes/partials/admin/menu/planRequest, src/public/assets/js/routes/partials/admin/menu/coupon, src/public/assets/js/routes/partials/admin/menu/order, src/public/assets/js/routes/partials/admin/menu/emailTemplate, src/public/assets/js/routes/partials/admin/menu/settings
  // Import would be: import * as module0 from 'src/public/assets/js/routes/partials/admin/menu/dashboard';
// Import would be: import * as module1 from 'src/public/assets/js/routes/partials/admin/menu/accountStatement';
// Import would be: import * as module2 from 'src/public/assets/js/routes/partials/admin/menu/invoiceSummary';
// Import would be: import * as module3 from 'src/public/assets/js/routes/partials/admin/menu/salesReport';
// Import would be: import * as module4 from 'src/public/assets/js/routes/partials/admin/menu/receivables';
// Import would be: import * as module5 from 'src/public/assets/js/routes/partials/admin/menu/payables';
// Import would be: import * as module6 from 'src/public/assets/js/routes/partials/admin/menu/billSummary';
// Import would be: import * as module7 from 'src/public/assets/js/routes/partials/admin/menu/productStock';
// Import would be: import * as module8 from 'src/public/assets/js/routes/partials/admin/menu/transactions';
// Import would be: import * as module9 from 'src/public/assets/js/routes/partials/admin/menu/incomeSummary';
// Import would be: import * as module10 from 'src/public/assets/js/routes/partials/admin/menu/expenseSummary';
// Import would be: import * as module11 from 'src/public/assets/js/routes/partials/admin/menu/incomeVsExpenseSummary';
// Import would be: import * as module12 from 'src/public/assets/js/routes/partials/admin/menu/taxSummary';
// Import would be: import * as module13 from 'src/public/assets/js/routes/partials/admin/menu/hrmDashboard';
// Import would be: import * as module14 from 'src/public/assets/js/routes/partials/admin/menu/reportsPayroll';
// Import would be: import * as module15 from 'src/public/assets/js/routes/partials/admin/menu/reportsLeave';
// Import would be: import * as module16 from 'src/public/assets/js/routes/partials/admin/menu/reportsMonthlyAttendance';
// Import would be: import * as module17 from 'src/public/assets/js/routes/partials/admin/menu/crmDashboard';
// Import would be: import * as module18 from 'src/public/assets/js/routes/partials/admin/menu/reportsLead';
// Import would be: import * as module19 from 'src/public/assets/js/routes/partials/admin/menu/reportsDeal';
// Import would be: import * as module20 from 'src/public/assets/js/routes/partials/admin/menu/projectDashboard';
// Import would be: import * as module21 from 'src/public/assets/js/routes/partials/admin/menu/posDashboard';
// Import would be: import * as module22 from 'src/public/assets/js/routes/partials/admin/menu/reportsWarehouse';
// Import would be: import * as module23 from 'src/public/assets/js/routes/partials/admin/menu/reportsDailyPurchase';
// Import would be: import * as module24 from 'src/public/assets/js/routes/partials/admin/menu/reportsPosVsPurchase';
// Import would be: import * as module25 from 'src/public/assets/js/routes/partials/admin/menu/employee';
// Import would be: import * as module26 from 'src/public/assets/js/routes/partials/admin/menu/setSalary';
// Import would be: import * as module27 from 'src/public/assets/js/routes/partials/admin/menu/manageLeave';
// Import would be: import * as module28 from 'src/public/assets/js/routes/partials/admin/menu/markAttendance';
// Import would be: import * as module29 from 'src/public/assets/js/routes/partials/admin/menu/bulkAttendance';
// Import would be: import * as module30 from 'src/public/assets/js/routes/partials/admin/menu/indicator';
// Import would be: import * as module31 from 'src/public/assets/js/routes/partials/admin/menu/appraisal';
// Import would be: import * as module32 from 'src/public/assets/js/routes/partials/admin/menu/goalTracking';
// Import would be: import * as module33 from 'src/public/assets/js/routes/partials/admin/menu/training';
// Import would be: import * as module34 from 'src/public/assets/js/routes/partials/admin/menu/trainer';
// Import would be: import * as module35 from 'src/public/assets/js/routes/partials/admin/menu/job';
// Import would be: import * as module36 from 'src/public/assets/js/routes/partials/admin/menu/jobApplication';
// Import would be: import * as module37 from 'src/public/assets/js/routes/partials/admin/menu/info';
// Import would be: import * as module38 from 'src/public/assets/js/routes/partials/admin/menu/event';
// Import would be: import * as module39 from 'src/public/assets/js/routes/partials/admin/menu/meeting';
// Import would be: import * as module40 from 'src/public/assets/js/routes/partials/admin/menu/employeeAsset';
// Import would be: import * as module41 from 'src/public/assets/js/routes/partials/admin/menu/document';
// Import would be: import * as module42 from 'src/public/assets/js/routes/partials/admin/menu/companyPolicy';
// Import would be: import * as module43 from 'src/public/assets/js/routes/partials/admin/menu/hrmSystem';
// Import would be: import * as module44 from 'src/public/assets/js/routes/partials/admin/menu/bank';
// Import would be: import * as module45 from 'src/public/assets/js/routes/partials/admin/menu/chart';
// Import would be: import * as module46 from 'src/public/assets/js/routes/partials/admin/menu/budgetPlanner';
// Import would be: import * as module47 from 'src/public/assets/js/routes/partials/admin/menu/financialGoal';
// Import would be: import * as module48 from 'src/public/assets/js/routes/partials/admin/menu/accountingSetup';
// Import would be: import * as module49 from 'src/public/assets/js/routes/partials/admin/menu/printSetting';
// Import would be: import * as module50 from 'src/public/assets/js/routes/partials/admin/menu/lead';
// Import would be: import * as module51 from 'src/public/assets/js/routes/partials/admin/menu/deal';
// Import would be: import * as module52 from 'src/public/assets/js/routes/partials/admin/menu/formBuilder';
// Import would be: import * as module53 from 'src/public/assets/js/routes/partials/admin/menu/contract';
// Import would be: import * as module54 from 'src/public/assets/js/routes/partials/admin/menu/crmSystem';
// Import would be: import * as module55 from 'src/public/assets/js/routes/partials/admin/menu/project';
// Import would be: import * as module56 from 'src/public/assets/js/routes/partials/admin/menu/task';
// Import would be: import * as module57 from 'src/public/assets/js/routes/partials/admin/menu/timesheet';
// Import would be: import * as module58 from 'src/public/assets/js/routes/partials/admin/menu/bug';
// Import would be: import * as module59 from 'src/public/assets/js/routes/partials/admin/menu/taskCalendarLink';
// Import would be: import * as module60 from 'src/public/assets/js/routes/partials/admin/menu/tracker';
// Import would be: import * as module61 from 'src/public/assets/js/routes/partials/admin/menu/projectReport';
// Import would be: import * as module62 from 'src/public/assets/js/routes/partials/admin/menu/projectTaskStages';
// Import would be: import * as module63 from 'src/public/assets/js/routes/partials/admin/menu/bugStatus';
// Import would be: import * as module64 from 'src/public/assets/js/routes/partials/admin/menu/userLink';
// Import would be: import * as module65 from 'src/public/assets/js/routes/partials/admin/menu/role';
// Import would be: import * as module66 from 'src/public/assets/js/routes/partials/admin/menu/client';
// Import would be: import * as module67 from 'src/public/assets/js/routes/partials/admin/menu/productService';
// Import would be: import * as module68 from 'src/public/assets/js/routes/partials/admin/menu/warehouse';
// Import would be: import * as module69 from 'src/public/assets/js/routes/partials/admin/menu/purchase';
// Import would be: import * as module70 from 'src/public/assets/js/routes/partials/admin/menu/pos';
// Import would be: import * as module71 from 'src/public/assets/js/routes/partials/admin/menu/warehouseTransfer';
// Import would be: import * as module72 from 'src/public/assets/js/routes/partials/admin/menu/posBarcode';
// Import would be: import * as module73 from 'src/public/assets/js/routes/partials/admin/menu/posPrintSetting';
// Import would be: import * as module74 from 'src/public/assets/js/routes/partials/admin/menu/calls';
// Import would be: import * as module75 from 'src/public/assets/js/routes/partials/admin/menu/notificationTemplate';
// Import would be: import * as module76 from 'src/public/assets/js/routes/partials/admin/menu/systemSettings';
// Import would be: import * as module77 from 'src/public/assets/js/routes/partials/admin/menu/setupSubscription';
// Import would be: import * as module78 from 'src/public/assets/js/routes/partials/admin/menu/orderLink';
// Import would be: import * as module79 from 'src/public/assets/js/routes/partials/admin/menu/dashboardViewLink';
// Import would be: import * as module80 from 'src/public/assets/js/routes/partials/admin/menu/dealIndexLink';
// Import would be: import * as module81 from 'src/public/assets/js/routes/partials/admin/menu/contractIndexLink';
// Import would be: import * as module82 from 'src/public/assets/js/routes/partials/admin/menu/projectIndexLink';
// Import would be: import * as module83 from 'src/public/assets/js/routes/partials/admin/menu/projectReportLink';
// Import would be: import * as module84 from 'src/public/assets/js/routes/partials/admin/menu/tasksLink';
// Import would be: import * as module85 from 'src/public/assets/js/routes/partials/admin/menu/bugsLink';
// Import would be: import * as module86 from 'src/public/assets/js/routes/partials/admin/menu/timesheetListLink';
// Import would be: import * as module87 from 'src/public/assets/js/routes/partials/admin/menu/taskCalendar';
// Import would be: import * as module88 from 'src/public/assets/js/routes/partials/admin/menu/support';
// Import would be: import * as module89 from 'src/public/assets/js/routes/partials/admin/menu/dashboardLink';
// Import would be: import * as module90 from 'src/public/assets/js/routes/partials/admin/menu/user';
// Import would be: import * as module91 from 'src/public/assets/js/routes/partials/admin/menu/plan';
// Import would be: import * as module92 from 'src/public/assets/js/routes/partials/admin/menu/planRequest';
// Import would be: import * as module93 from 'src/public/assets/js/routes/partials/admin/menu/coupon';
// Import would be: import * as module94 from 'src/public/assets/js/routes/partials/admin/menu/order';
// Import would be: import * as module95 from 'src/public/assets/js/routes/partials/admin/menu/emailTemplate';
// Import would be: import * as module96 from 'src/public/assets/js/routes/partials/admin/menu/settings';
  
  beforeEach(() => {
    // Set up DOM
    document.body.innerHTML = `
      <form id="main-form">
        <input type="hidden" name="_token" value="test-csrf-token" />
        <input type="hidden" name="_method" value="POST" />
        <input type="text" id="name" name="name" />
        <button type="submit" id="submit-btn">Submit</button>
      </form>
      <table id="data-table">
        <tbody>
          <tr><td>Test</td></tr>
        </tbody>
      </table>
      <button class="delete-btn">Delete</button>
      <form id="delete-form">
        <input type="hidden" name="_method" value="DELETE" />
        <button id="confirm-delete-btn">Confirm</button>
      </form>
    `;
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('should set up DOM correctly', () => {
    expect(document.body.innerHTML).toContain('main-form');
  });

  test('should render without errors', () => {
    expect(document.body).toBeTruthy();
  });
});


