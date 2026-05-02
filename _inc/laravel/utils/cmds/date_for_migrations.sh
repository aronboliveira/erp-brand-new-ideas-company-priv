#!/bin/bash

# Navigate to migrations directory
cd database/migrations

# Get current timestamp as base
base_date=$(date +%Y_%m_%d_%H%M%S)
counter=0

echo "Renaming migration files with current timestamp..."

# Process all PHP files
for file in *.php; do
    if [ -f "$file" ]; then
        # Calculate new timestamp (base + counter for uniqueness)
        new_timestamp=$(date -d "@$(($(date -d "$(echo $base_date | sed 's/_/-/g; s/\(.*\)_\(.*\)/\1 \2/; s/\(..\)\(..\)\(..\)/\1:\2:\3/')" +%s) + counter))" +%Y_%m_%d_%H%M%S)

        # Check if file has timestamp format
        if [[ $file =~ ^[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_ ]]; then
            # File has timestamp - replace it
            new_name=$(echo "$file" | sed "s/^[0-9]\{4\}_[0-9]\{2\}_[0-9]\{2\}_[0-9]\{6\}_/${new_timestamp}_/")
        else
            # File doesn't have timestamp - add it
            new_name="${new_timestamp}_${file}"
        fi

        # Rename if different
        if [ "$file" != "$new_name" ]; then
            mv "$file" "$new_name"
            echo "✓ Renamed: $file -> $new_name"
        else
            echo "- Skipped: $file (no change needed)"
        fi

        ((counter++))
    fi
done

echo ""
echo "Done! All migration files now have current timestamps."
echo "Remember to clear your migration history if needed:"
echo "php artisan tinker"
echo "DB::table('migrations')->truncate();"