# Execute the CheckTableExistsCommand
sudo docker compose exec willowcms bin/cake check_table_exists settings

# Check the exit status
if [ $? -eq 0 ]; then
    echo "The table exists."
    # Perform actions when the table exists
elif [ $? -eq 1 ]; then
    echo "The table does not exist."
    # Perform actions when the table does not exist
fi