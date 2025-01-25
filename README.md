### Restaurant Billing System

#### **1. System Overview**
- Allows clients to order food and beverages.
- Food categories: **Appetizer**, **Main Course**, **Dessert**.
- Beverages: **Hot** and **Cold drinks**.
- Calculates total amount payable, including discounts and taxes.

---

#### **2. Features**
- **Order Details**:
  - Unit price per food/beverage.
  - Quantity ordered per table.  
- **Discounts**:
  - **5% Discount**: If all types of food (Appetizer, Main Course, Dessert) are ordered (excluding beverages).  
  - **2% Discount**: If more than 5 items (including beverages) are ordered.  
- **Taxes**:
  - **8% Sales and Service Tax (SST)**: Applied to the total amount after discounts.

---

#### **3. Example Calculations**
- **Sample 1**:
  - Items: 1 Appetizer, 1 Main Course, 2 Desserts, 2 Beverages.
  - All food types selected: **YES** (5% discount).  
  - Total items: 6 (2% additional discount).  
  - SST: 8%.  

- **Sample 2**:
  - Items: 0 Appetizers, 1 Main Course, 0 Desserts, 1 Beverage.
  - All food types selected: **NO** (No discount).  
  - Total items: 2 (No additional discount).  
  - SST: 8%.  

- **Sample 3**:
  - Items: 2 Appetizers, 3 Main Courses, 0 Desserts, 3 Beverages.
  - All food types selected: **NO** (No 5% discount).  
  - Total items: 8 (2% additional discount).  
  - SST: 8%.  

---

#### **How It Works**
1. Input: Food/beverage categories, unit prices, and quantities.
2. Check eligibility for **5%** and **2%** discounts.
3. Apply **8% SST** to the discounted total.
4. Output: Total payable amount for the customer.
