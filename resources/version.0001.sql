-- Add the completed field to the todos table
ALTER TABLE todos
    ADD completed BOOLEAN default false;
