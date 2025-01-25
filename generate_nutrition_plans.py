import mysql.connector
import math

class NutritionPlanner:
    def __init__(self):
        self.db_config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'fitnesspal'
        }
        
        # Create nutrition_plans table if it doesn't exist
        self.create_nutrition_table()
    
    def create_nutrition_table(self):
        conn = mysql.connector.connect(**self.db_config)
        cursor = conn.cursor()
        
        create_table_query = """
        CREATE TABLE IF NOT EXISTS nutrition_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            daily_calories INT,
            protein_g INT,
            carbs_g INT,
            fats_g INT,
            breakfast TEXT,
            lunch TEXT,
            dinner TEXT,
            snacks TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
        """
        cursor.execute(create_table_query)
        conn.commit()
        cursor.close()
        conn.close()

    def calculate_calories(self, weight, height, age, gender, goal):
        # Calculate BMR using Mifflin-St Jeor Equation
        if gender.lower() == 'male':
            bmr = (10 * weight) + (6.25 * height) - (5 * age) + 5
        else:
            bmr = (10 * weight) + (6.25 * height) - (5 * age) - 161
        
        # Activity factor (using moderate activity as default)
        activity_factor = 1.55
        maintenance_calories = bmr * activity_factor
        
        # Adjust calories based on goal
        if goal == 'weight_loss':
            daily_calories = maintenance_calories - 500  # Create a deficit
        elif goal == 'muscle_gain':
            daily_calories = maintenance_calories + 300  # Create a surplus
        else:
            daily_calories = maintenance_calories
            
        return round(daily_calories)

    def calculate_macros(self, calories, goal):
        if goal == 'weight_loss':
            protein_pct = 0.40  # Higher protein for muscle preservation
            fat_pct = 0.30
            carb_pct = 0.30
        elif goal == 'muscle_gain':
            protein_pct = 0.30
            fat_pct = 0.25
            carb_pct = 0.45
        else:  # maintenance
            protein_pct = 0.30
            fat_pct = 0.30
            carb_pct = 0.40
            
        return {
            'protein': round((calories * protein_pct) / 4),  # 4 calories per gram
            'carbs': round((calories * carb_pct) / 4),
            'fats': round((calories * fat_pct) / 9)  # 9 calories per gram
        }

    def generate_meal_plan(self, goal, calories):
        meal_plans = {
            'weight_loss': {
                'breakfast': 'Egg white omelet with spinach (300 cal)\nOatmeal with berries (200 cal)',
                'lunch': 'Grilled chicken salad with light dressing (400 cal)\nQuinoa (150 cal)',
                'dinner': 'Baked salmon (350 cal)\nSteamed vegetables (100 cal)\nBrown rice (150 cal)',
                'snacks': 'Greek yogurt with nuts (200 cal)\nProtein shake (150 cal)'
            },
            'muscle_gain': {
                'breakfast': 'Whole eggs omelet with cheese (450 cal)\nOatmeal with peanut butter (350 cal)',
                'lunch': 'Chicken breast with rice (500 cal)\nSweet potato (200 cal)\nMixed vegetables (100 cal)',
                'dinner': 'Lean beef steak (400 cal)\nQuinoa (200 cal)\nRoasted vegetables (150 cal)',
                'snacks': 'Protein shake with banana (300 cal)\nAlmonds and dried fruits (250 cal)'
            },
            'maintenance': {
                'breakfast': 'Scrambled eggs (300 cal)\nWhole grain toast (200 cal)\nFruit (100 cal)',
                'lunch': 'Turkey sandwich (400 cal)\nYogurt (150 cal)\nApple (80 cal)',
                'dinner': 'Grilled fish (300 cal)\nBrown rice (200 cal)\nSalad (100 cal)',
                'snacks': 'Hummus with carrots (200 cal)\nHandful of nuts (170 cal)'
            }
        }
        return meal_plans[goal]

    def create_nutrition_plan(self, user_id):
        try:
            # Get user data
            conn = mysql.connector.connect(**self.db_config)
            cursor = conn.cursor(dictionary=True)
            
            cursor.execute("""
                SELECT age, gender, weight, height, goal 
                FROM fitness_data 
                WHERE user_id = %s
            """, (user_id,))
            
            user_data = cursor.fetchone()
            
            if user_data:
                # Calculate daily calories
                daily_calories = self.calculate_calories(
                    float(user_data['weight']),
                    float(user_data['height']),
                    int(user_data['age']),
                    user_data['gender'],
                    user_data['goal']
                )
                
                # Calculate macros
                macros = self.calculate_macros(daily_calories, user_data['goal'])
                
                # Generate meal plan
                meals = self.generate_meal_plan(user_data['goal'], daily_calories)
                
                # Store in database
                cursor.execute("""
                    INSERT INTO nutrition_plans 
                    (user_id, daily_calories, protein_g, carbs_g, fats_g, breakfast, lunch, dinner, snacks)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                    ON DUPLICATE KEY UPDATE
                    daily_calories = VALUES(daily_calories),
                    protein_g = VALUES(protein_g),
                    carbs_g = VALUES(carbs_g),
                    fats_g = VALUES(fats_g),
                    breakfast = VALUES(breakfast),
                    lunch = VALUES(lunch),
                    dinner = VALUES(dinner),
                    snacks = VALUES(snacks)
                """, (
                    user_id, daily_calories, 
                    macros['protein'], macros['carbs'], macros['fats'],
                    meals['breakfast'], meals['lunch'], meals['dinner'], meals['snacks']
                ))
                
                conn.commit()
                
            cursor.close()
            conn.close()
            
        except Exception as e:
            print(f"Error creating nutrition plan: {e}")

# Usage
if __name__ == "__main__":
    planner = NutritionPlanner()
    # This will create nutrition plans for all users
    conn = mysql.connector.connect(**planner.db_config)
    cursor = conn.cursor()
    cursor.execute("SELECT id FROM users")
    users = cursor.fetchall()
    for user in users:
        planner.create_nutrition_plan(user[0])
    cursor.close()
    conn.close()