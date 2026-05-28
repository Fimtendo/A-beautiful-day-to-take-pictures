-- Photo Posts Schema
-- Run this in Supabase SQL Editor

-- Create photo_posts table
CREATE TABLE IF NOT EXISTS photo_posts (
  id BIGSERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  title TEXT,
  description TEXT,
  image_url TEXT NOT NULL,
  marker_id INTEGER REFERENCES markers(id),
  lat DOUBLE PRECISION,
  lng DOUBLE PRECISION,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create photo_post_likes table
CREATE TABLE IF NOT EXISTS photo_post_likes (
  id BIGSERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  post_id BIGINT REFERENCES photo_posts(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(user_id, post_id)
);

-- Create photo_post_bookmarks table
CREATE TABLE IF NOT EXISTS photo_post_bookmarks (
  id BIGSERIAL PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) NOT NULL,
  post_id BIGINT REFERENCES photo_posts(id) ON DELETE CASCADE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  UNIQUE(user_id, post_id)
);

-- Enable RLS
ALTER TABLE photo_posts ENABLE ROW LEVEL SECURITY;
ALTER TABLE photo_post_likes ENABLE ROW LEVEL SECURITY;
ALTER TABLE photo_post_bookmarks ENABLE ROW LEVEL SECURITY;

-- RLS Policies for photo_posts
CREATE POLICY "Everyone can view photo posts" ON photo_posts FOR SELECT USING (true);
CREATE POLICY "Authenticated users can insert photo posts" ON photo_posts FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can update own photo posts" ON photo_posts FOR UPDATE USING (auth.uid() = user_id);
CREATE POLICY "Users can delete own photo posts" ON photo_posts FOR DELETE USING (auth.uid() = user_id);

-- RLS Policies for photo_post_likes
CREATE POLICY "Everyone can view photo post likes" ON photo_post_likes FOR SELECT USING (true);
CREATE POLICY "Authenticated users can insert own likes" ON photo_post_likes FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can delete own likes" ON photo_post_likes FOR DELETE USING (auth.uid() = user_id);

-- RLS Policies for photo_post_bookmarks
CREATE POLICY "Users can view own bookmarks" ON photo_post_bookmarks FOR SELECT USING (auth.uid() = user_id);
CREATE POLICY "Authenticated users can insert own bookmarks" ON photo_post_bookmarks FOR INSERT WITH CHECK (auth.uid() = user_id);
CREATE POLICY "Users can delete own bookmarks" ON photo_post_bookmarks FOR DELETE USING (auth.uid() = user_id);

-- Photo Dates Schema (for scheduling photo shoots)
-- Create photo_dates table
CREATE TABLE IF NOT EXISTS photo_dates (
  id BIGSERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  start_time TIMESTAMP WITH TIME ZONE NOT NULL,
  end_time TIMESTAMP WITH TIME ZONE NOT NULL,
  capacity INTEGER,
  marker_id INTEGER REFERENCES markers(id),
  marker_name TEXT,
  lat DOUBLE PRECISION,
  lng DOUBLE PRECISION,
  created_by UUID REFERENCES auth.users(id) NOT NULL,
  created_by_username TEXT,
  attendees JSONB DEFAULT '[]'::jsonb,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Enable RLS for photo_dates
ALTER TABLE photo_dates ENABLE ROW LEVEL SECURITY;

-- RLS Policies for photo_dates
CREATE POLICY "Everyone can view photo dates" ON photo_dates FOR SELECT USING (true);
CREATE POLICY "Authenticated users can insert photo dates" ON photo_dates FOR INSERT WITH CHECK (auth.uid() = created_by);
CREATE POLICY "Users can update own photo dates" ON photo_dates FOR UPDATE USING (auth.uid() = created_by);
CREATE POLICY "Authenticated users can join photo dates" ON photo_dates FOR UPDATE USING (auth.uid() IS NOT NULL);
CREATE POLICY "Users can delete own photo dates" ON photo_dates FOR DELETE USING (auth.uid() = created_by);