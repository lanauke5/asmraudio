USE asmraudio;
INSERT IGNORE INTO categories(name,slug,description) VALUES
('ASMR Guides','asmr-guides','Beginner-friendly explanations and practical ASMR guidance.'),
('ASMR Triggers','asmr-triggers','Explore whispering, tapping, brushing, and other triggers.'),
('Sleep Sounds','sleep-sounds','Relaxing audio and sound guidance for better rest.'),
('Focus & Relaxation','focus-relaxation','Calm backgrounds for focus, study, and unwinding.'),
('ASMR Equipment','asmr-equipment','Microphones, headphones, recorders, and accessories.'),
('ASMR Creators','asmr-creators','Profiles and resources about ASMR creators.');
INSERT IGNORE INTO articles(title,slug,excerpt,content,category,status) VALUES
('How Does ASMR Work?','how-does-asmr-work','A draft guide explaining common ASMR responses and triggers.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','ASMR Guides','draft'),
('Best ASMR Triggers for Relaxation','best-asmr-triggers-relaxation','A draft comparison of popular relaxation triggers.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','ASMR Triggers','draft'),
('Whispering ASMR Explained','whispering-asmr','A draft guide to whispering ASMR.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','ASMR Triggers','draft'),
('Tapping ASMR for Sleep','tapping-asmr-for-sleep','A draft guide to tapping sounds for sleep routines.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','Sleep Sounds','draft'),
('Best ASMR Sounds for Sleep','best-asmr-sounds-for-sleep','A draft guide to choosing sleep sounds.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','Sleep Sounds','draft'),
('Rain Sounds for Sleeping','rain-sounds-for-sleeping','A draft guide to rain recordings and sleep.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','Sleep Sounds','draft'),
('White Noise vs Brown Noise','white-noise-vs-brown-noise','A draft comparison of white and brown noise.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','Sleep Sounds','draft'),
('ASMR for Focus and Studying','asmr-for-focus-and-studying','A draft guide to calm study backgrounds.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','Focus & Relaxation','draft'),
('Do You Need Headphones for ASMR?','do-you-need-headphones-for-asmr','A draft guide to headphones and ASMR listening.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','ASMR Equipment','draft'),
('Best Headphones for ASMR','best-headphones-for-asmr','A draft equipment guide for ASMR listeners.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','ASMR Equipment','draft'),
('Best ASMR Microphones for Beginners','best-asmr-microphones-for-beginners','A draft microphone buying guide.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','ASMR Equipment','draft'),
('How to Start an ASMR YouTube Channel','start-asmr-youtube-channel','A draft creator guide for planning an ASMR channel.','<h2>Draft article</h2><p>Complete and fact-check this article before publishing.</p>','ASMR Creators','draft');
